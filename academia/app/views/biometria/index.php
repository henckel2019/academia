<?php declare(strict_types=1);
ob_start(); ?>
<div class="page-header">
    <div><span class="eyebrow">Celular da recepção</span>
        <h1>Cadastro facial</h1>
        <p>Cadastre o rosto no mesmo celular usado para registrar as entradas.</p>
    </div><a class="btn btn-secondary" href="<?= e(url('/checkin')) ?>">Abrir check-in</a>
</div>
<div class="checkin-layout">
    <section class="panel" data-facial data-mode="enroll" data-models="<?= e(asset('/vendor/face-api/models')) ?>"
        data-checkin="<?= e(url('/checkin')) ?>" data-csrf="<?= e(\Academia\Core\Csrf::token()) ?>"
        data-scope="<?= e(url('/')) ?>">
        <label for="face-student">Aluno<select id="face-student" data-face-student>
                <option value="">Selecione o aluno</option><?php foreach ($students as $student): ?><option
                    value="<?= e($student['id']) ?>"><?= e($student['nome']) ?> · <?= e($student['matricula_codigo']) ?>
                </option><?php endforeach ?>
            </select></label>
        <p>Os dados faciais ficam apenas neste navegador. Nenhuma foto é salva. Ao limpar os dados do site ou trocar de
            celular, cadastre novamente.</p>
        <label class="consent-label"><input type="checkbox" data-face-consent> O aluno ou responsável autorizou o
            cadastro facial e foi informado da alternativa por matrícula.</label>
        <div class="camera-frame"><video data-face-video autoplay muted playsinline
                aria-label="Câmera para cadastro facial"></video></div>
        <p data-face-status role="status" aria-live="polite">Selecione o aluno e abra a câmera.</p>
        <div class="form-actions"><button class="btn btn-primary" type="button" data-face-start>Abrir
                câmera</button><button class="btn btn-primary" type="button" data-face-enroll disabled>Capturar e
                cadastrar</button><button class="btn btn-secondary" type="button" data-face-stop disabled>Desligar
                câmera</button><button class="btn btn-secondary" type="button" data-face-delete>Excluir facial do
                aluno</button></div>
        <p class="hint" data-face-count></p>
    </section>
    <section class="panel"><span class="eyebrow">Antes do primeiro treino</span>
        <h2>O rosto fica neste celular.</h2>
        <p>Selecione o aluno, confirme a autorização e capture três amostras com boa iluminação.</p>
        <h2>Uso na recepção</h2>
        <p>Use este celular sob supervisão da equipe. O reconhecimento por câmera não possui prova de vida certificada e
            pode ser enganado por fotografias. Não use como fechadura ou liberação física automática.</p>
    </section>
</div>
<script defer src="<?= e(asset('/vendor/face-api/face-api.min.js')) ?>"></script>
<script type="module" src="<?= e(asset('/js/facial.js')) ?>"></script>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/main.php'; ?>
