import { openFacialStore } from './facial-store.js';
import { matchFace, descriptorDistance } from './facial-match.mjs';

const root = document.querySelector('[data-facial]');
if (root) initializeFacial(root);

function initializeFacial(root) {
    const video = root.querySelector('[data-face-video]');
    const status = root.querySelector('[data-face-status]');
    const start = root.querySelector('[data-face-start]');
    const stop = root.querySelector('[data-face-stop]');
    const enroll = root.querySelector('[data-face-enroll]');
    const select = root.querySelector('[data-face-student]');
    const result = root.querySelector('[data-face-result]');
    let store;
    let faces = [];
    let stream;
    let active = false;
    let generation = 0;
    let lastStudent = null;
    let consecutive = 0;
    let lastAttendance = 0;
    let modelsLoaded = false;
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const tell = (message) => {
        status.textContent = message;
    };

    async function refresh() {
        store ??= await openFacialStore(root.dataset.scope);
        faces = await store.list();
        root.querySelector('[data-face-count]').textContent =
            `${faces.length} aluno(s) com facial cadastrada neste navegador.`;
    }
    function shutdown() {
        active = false;
        generation += 1;
        stream?.getTracks().forEach((track) => track.stop());
        video.srcObject = null;
        start.disabled = false;
        stop.disabled = true;
        if (enroll) enroll.disabled = true;
    }
    async function detect() {
        const detections = await faceapi
            .detectAllFaces(
                video,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
            )
            .withFaceLandmarks(true)
            .withFaceDescriptors();
        if (detections.length !== 1) {
            tell(
                detections.length > 1
                    ? 'Há mais de um rosto. Entre um aluno de cada vez.'
                    : 'Posicione o rosto de frente para a câmera.'
            );
            return null;
        }
        return detections[0].descriptor;
    }
    async function registerAttendance(studentId) {
        tell('Consultando matrícula e pagamentos…');
        const body = new URLSearchParams({
            _csrf: root.dataset.csrf,
            aluno_id: studentId,
            origem: 'BIOMETRIA',
            response: 'json'
        });
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 20000);
        let response;
        try {
            response = await fetch(root.dataset.checkin, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                signal: abort.signal
            });
        } finally {
            clearTimeout(timeout);
        }
        if (
            !response.ok ||
            response.redirected ||
            !response.headers.get('content-type')?.includes('application/json')
        ) {
            throw new Error(
                'Não foi possível confirmar a presença. Verifique a conexão e atualize a página para entrar novamente.'
            );
        }
        const decision = await response.json();
        if (!active) return;
        result.replaceChildren();
        result.className = `face-result ${decision.allowed ? 'access-allowed' : 'access-blocked'}`;
        for (const text of [decision.title, decision.student, decision.message]) {
            const line = document.createElement('p');
            line.textContent = text ?? '';
            result.append(line);
        }
        tell('Resultado confirmado pelo servidor. Próximo aluno.');
    }
    async function recognize(currentGeneration) {
        while (active && currentGeneration === generation) {
            const descriptor = await detect();
            if (!active || currentGeneration !== generation) return;
            const studentId = descriptor ? matchFace(descriptor, faces) : null;
            if (!studentId) {
                consecutive = 0;
                lastStudent = null;
                if (descriptor)
                    tell(
                        'Rosto não reconhecido com segurança. Use a matrícula ou cadastre a facial.'
                    );
            } else {
                consecutive = studentId === lastStudent ? consecutive + 1 : 1;
                lastStudent = studentId;
                if (consecutive >= 2 && Date.now() - lastAttendance > 8000) {
                    await registerAttendance(studentId);
                    lastAttendance = Date.now();
                    consecutive = 0;
                }
            }
            await sleep(1200);
        }
    }
    start.addEventListener('click', async () => {
        start.disabled = true;
        const currentGeneration = generation;
        try {
            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                throw new Error(
                    'A câmera precisa de HTTPS e de um navegador compatível. Abra o endereço HTTPS da academia ou use a matrícula.'
                );
            }
            if (
                root.dataset.mode === 'enroll' &&
                (!select.value || !root.querySelector('[data-face-consent]').checked)
            ) {
                throw new Error(
                    'Selecione o aluno e registre a autorização antes de abrir a câmera.'
                );
            }
            await refresh();
            if (root.dataset.mode === 'checkin' && !faces.length)
                throw new Error('Nenhuma facial neste celular. Abra Cadastrar facial primeiro.');
            tell(
                'Carregando reconhecimento facial. A primeira abertura pode levar alguns segundos…'
            );
            if (!window.faceapi)
                throw new Error(
                    'O módulo facial não carregou. Atualize a página e tente novamente.'
                );
            if (!modelsLoaded) {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(root.dataset.models),
                    faceapi.nets.faceLandmark68TinyNet.loadFromUri(root.dataset.models),
                    faceapi.nets.faceRecognitionNet.loadFromUri(root.dataset.models)
                ]);
                modelsLoaded = true;
            }
            if (currentGeneration !== generation || document.hidden) return;
            const cameraStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: 'user',
                    width: { ideal: 480 },
                    height: { ideal: 360 },
                    frameRate: { ideal: 10, max: 15 }
                }
            });
            if (currentGeneration !== generation || document.hidden) {
                cameraStream.getTracks().forEach((track) => track.stop());
                return;
            }
            stream = cameraStream;
            video.srcObject = stream;
            await video.play();
            active = true;
            stop.disabled = false;
            if (enroll) enroll.disabled = false;
            tell('Câmera pronta. Olhe de frente para a lente.');
            if (root.dataset.mode === 'checkin') await recognize(generation);
        } catch (error) {
            shutdown();
            tell(
                error.name === 'NotAllowedError'
                    ? 'Câmera bloqueada. Permita o acesso nas configurações do navegador ou use a matrícula.'
                    : error.message
            );
        }
    });
    enroll?.addEventListener('click', async () => {
        const studentId = select.value;
        if (!studentId || !root.querySelector('[data-face-consent]').checked || !active) {
            tell('Selecione o aluno, confirme a autorização e abra a câmera.');
            return;
        }
        enroll.disabled = true;
        const currentGeneration = generation;
        try {
            const descriptors = [];
            for (let attempt = 0; attempt < 3; attempt += 1) {
                tell(`Capturando amostra ${attempt + 1} de 3. Mantenha o rosto visível.`);
                const descriptor = await detect();
                if (!active || currentGeneration !== generation) return;
                if (!descriptor)
                    throw new Error(
                        'Não foi possível capturar um único rosto. Ajuste a luz e tente novamente.'
                    );
                if (descriptors.length && descriptorDistance(descriptor, descriptors[0]) > 0.45)
                    throw new Error(
                        'As amostras não correspondem ao mesmo rosto. Repita o cadastro.'
                    );
                if (
                    matchFace(
                        descriptor,
                        faces.filter((face) => face.studentId !== studentId)
                    )
                )
                    throw new Error(
                        'Este rosto já corresponde a outro aluno neste celular. Confira os cadastros.'
                    );
                descriptors.push(Array.from(descriptor));
                await sleep(500);
            }
            if (studentId !== select.value || !root.querySelector('[data-face-consent]').checked)
                throw new Error(
                    'O aluno ou a autorização mudou durante a captura. Repita o cadastro.'
                );
            await store.save(studentId, descriptors);
            await refresh();
            shutdown();
            tell('Facial cadastrada neste celular. Abra o check-in para registrar as entradas.');
        } catch (error) {
            tell(error.message);
        } finally {
            enroll.disabled = !active;
        }
    });
    root.querySelector('[data-face-delete]')?.addEventListener('click', async () => {
        if (!select.value) return tell('Selecione o aluno para excluir a facial.');
        if (!confirm('Excluir o cadastro facial deste aluno neste celular?')) return;
        try {
            await refresh();
            await store.remove(select.value);
            await refresh();
            tell('Cadastro facial excluído deste celular.');
        } catch (error) {
            tell(error.message);
        }
    });
    stop.addEventListener('click', () => {
        shutdown();
        tell('Câmera desligada.');
    });
    root.querySelector('[data-face-fullscreen]')?.addEventListener('click', async () => {
        try {
            await root.requestFullscreen();
        } catch {
            tell('Tela cheia indisponível neste navegador. Você pode continuar nesta tela.');
        }
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            shutdown();
            tell('Câmera pausada. Toque em iniciar ao retornar.');
        }
    });
    window.addEventListener('pagehide', shutdown);
    refresh().catch((error) => tell(error.message));
}
