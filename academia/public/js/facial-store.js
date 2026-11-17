export async function openFacialStore(scope) {
    const db = await new Promise((resolve, reject) => {
        const request = indexedDB.open('garagem-aco-facial', 1);
        request.onupgradeneeded = () =>
            request.result.createObjectStore('faces', { keyPath: 'key' });
        request.onsuccess = () => resolve(request.result);
        request.onerror = () =>
            reject(new Error('Não foi possível abrir o armazenamento facial neste navegador.'));
    });
    function run(mode, operation) {
        return new Promise((resolve, reject) => {
            const transaction = db.transaction('faces', mode);
            const request = operation(transaction.objectStore('faces'));
            transaction.oncomplete = () => resolve(request.result);
            transaction.onerror = () =>
                reject(new Error('Falha ao salvar ou ler o cadastro facial.'));
            transaction.onabort = () =>
                reject(new Error('O armazenamento facial foi interrompido.'));
        });
    }
    return {
        async list() {
            const rows = await run('readonly', (store) => store.getAll());
            return rows.filter((row) => row.scope === scope);
        },
        save(studentId, descriptors) {
            return run('readwrite', (store) =>
                store.put({
                    key: `${scope}:${studentId}`,
                    scope,
                    studentId,
                    descriptors,
                    consentAt: new Date().toISOString()
                })
            );
        },
        remove(studentId) {
            return run('readwrite', (store) => store.delete(`${scope}:${studentId}`));
        }
    };
}
