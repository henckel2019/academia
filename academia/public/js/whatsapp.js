(() => {
    const root = document.querySelector('[data-whatsapp]');
    if (!root) return;
    const labels = {
        OFFLINE: 'Serviço parado',
        DISCONNECTED: 'Desconectado',
        CONNECTING: 'Conectando',
        QR: 'Leia o QR Code',
        AUTHENTICATED: 'Confirmando conexão',
        READY: 'Conectado',
        ERROR: 'Falha na conexão'
    };
    let loading = false;
    async function refresh() {
        if (loading || document.hidden) return;
        loading = true;
        try {
            const response = await fetch(root.dataset.statusUrl, {
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (
                !response.ok ||
                response.redirected ||
                !response.headers.get('content-type')?.includes('application/json')
            )
                throw new Error('A sessão expirou. Atualize a página e entre novamente.');
            const state = await response.json();
            root.querySelector('[data-whatsapp-state]').textContent =
                labels[state.state] ?? state.state;
            root.querySelector('[data-whatsapp-status]').textContent =
                state.error ??
                (state.state === 'READY'
                    ? `Conta conectada: +${state.account}. Pronta para os lembretes de mensalidade.`
                    : 'Conecte a conta e aguarde o QR Code nesta tela.');
            const qr = root.querySelector('[data-whatsapp-qr]');
            qr.hidden = !state.qr;
            if (state.qr?.startsWith('data:image/png;base64,')) qr.src = state.qr;
            else qr.removeAttribute('src');
        } catch (error) {
            root.querySelector('[data-whatsapp-status]').textContent = error.message;
        } finally {
            loading = false;
        }
    }
    void refresh();
    const timer = setInterval(refresh, 3000);
    window.addEventListener('pagehide', () => clearInterval(timer));
})();
