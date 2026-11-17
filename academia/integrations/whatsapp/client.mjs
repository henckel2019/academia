import whatsapp from 'whatsapp-web.js';
import QRCode from 'qrcode';
import { existsSync } from 'node:fs';

export function createWhatsAppClient({ storage, executablePath, journal }) {
    let client;
    let busy = false;
    let state = { state: 'DISCONNECTED', qr: null, account: null, error: null };
    let sendTail = Promise.resolve();
    async function connect() {
        if (busy || ['READY', 'CONNECTING', 'QR', 'AUTHENTICATED'].includes(state.state)) return;
        busy = true;
        state = { state: 'CONNECTING', qr: null, account: null, error: null };
        try {
            if (client) await client.destroy();
            client = new whatsapp.Client({
                authStrategy: new whatsapp.LocalAuth({ dataPath: storage }),
                puppeteer: { headless: true, ...(executablePath ? { executablePath } : {}) },
                webVersionCache: { type: 'local', path: `${storage}/web-cache` }
            });
            client.on('qr', (qr) => {
                QRCode.toDataURL(qr, { width: 300, margin: 3 })
                    .then((png) => {
                        if (state.state !== 'READY')
                            state = { state: 'QR', qr: png, account: null, error: null };
                    })
                    .catch(() => {
                        state = {
                            state: 'ERROR',
                            qr: null,
                            account: null,
                            error: 'Não foi possível gerar o QR Code.'
                        };
                    });
            });
            client.on('authenticated', () => {
                state = { state: 'AUTHENTICATED', qr: null, account: null, error: null };
            });
            client.on('ready', () => {
                state = {
                    state: 'READY',
                    qr: null,
                    account: client.info?.wid?.user ?? null,
                    error: null
                };
            });
            client.on('auth_failure', () => {
                state = {
                    state: 'ERROR',
                    qr: null,
                    account: null,
                    error: 'A conexão expirou. Conecte novamente.'
                };
            });
            client.on('disconnected', () => {
                state = {
                    state: 'DISCONNECTED',
                    qr: null,
                    account: null,
                    error: 'WhatsApp desconectado. Conecte novamente para retomar os envios.'
                };
            });
            await client.initialize();
        } catch (error) {
            state = {
                state: 'ERROR',
                qr: null,
                account: null,
                error: 'Não foi possível abrir o WhatsApp Web. Verifique o navegador instalado e a conexão com a internet.'
            };
            process.stderr.write(`WhatsApp initialization failed: ${error.name}\n`);
        } finally {
            busy = false;
        }
    }
    async function deliver(payload) {
        if (state.state !== 'READY')
            throw Object.assign(new Error('WhatsApp desconectado. Nenhuma mensagem foi enviada.'), {
                uncertain: false
            });
        const destination = await client.getNumberId(payload.phone);
        if (!destination)
            throw Object.assign(new Error('Este telefone não foi encontrado no WhatsApp.'), {
                uncertain: false
            });
        return journal.send(payload, async () => {
            const sent = await client.sendMessage(destination._serialized, payload.message, {
                sendSeen: false
            });
            return sent.id._serialized;
        });
    }
    return {
        status: () => ({ ...state }),
        connect,
        hasSession: () => existsSync(`${storage}/session`),
        async disconnect() {
            if (busy)
                throw Object.assign(
                    new Error('A conexão ainda está sendo preparada. Aguarde alguns segundos.'),
                    { uncertain: false }
                );
            if (client) {
                if (['READY', 'AUTHENTICATED'].includes(state.state)) await client.logout();
                else await client.destroy();
                client = undefined;
            }
            state = { state: 'DISCONNECTED', qr: null, account: null, error: null };
        },
        send(payload) {
            const next = sendTail.then(() => deliver(payload));
            sendTail = next.catch(() => undefined);
            return next;
        },
        async close() {
            if (client) await client.destroy();
        }
    };
}
