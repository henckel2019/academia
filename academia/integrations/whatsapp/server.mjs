import { createServer } from 'node:http';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { randomBytes, timingSafeEqual } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { resolve } from 'node:path';
import { createSendJournal } from './send-journal.mjs';
import { createWhatsAppClient } from './client.mjs';
import { startBillingScheduler } from './scheduler.mjs';

const project = fileURLToPath(new URL('../../', import.meta.url));
const storage = resolve(project, 'storage/whatsapp');
await mkdir(storage, { recursive: true, mode: 0o700 });
const tokenFile = resolve(storage, 'bridge-token');
if (!existsSync(tokenFile))
    await writeFile(tokenFile, randomBytes(32).toString('hex'), { mode: 0o600, flag: 'wx' });
const token = process.env.WHATSAPP_BRIDGE_TOKEN ?? (await readFile(tokenFile, 'utf8')).trim();
if (token.length < 32) throw new Error('O token do serviço deve ter pelo menos 32 caracteres.');
const chrome =
    process.env.WHATSAPP_CHROME_PATH ??
    (process.platform === 'win32'
        ? 'C:/Program Files/Google/Chrome/Application/chrome.exe'
        : undefined);
const client = createWhatsAppClient({
    storage: resolve(storage, 'auth'),
    executablePath: chrome && existsSync(chrome) ? chrome : undefined,
    journal: await createSendJournal(resolve(storage, 'send-journal.json'))
});
function authorized(header) {
    const supplied = Buffer.from((header ?? '').replace(/^Bearer /, ''));
    const expected = Buffer.from(token);
    return supplied.length === expected.length && timingSafeEqual(supplied, expected);
}
const server = createServer(async (request, response) => {
    const reply = (code, value) => {
        response.writeHead(code, {
            'Content-Type': 'application/json',
            'Cache-Control': 'no-store'
        });
        response.end(JSON.stringify(value));
    };
    if (!authorized(request.headers.authorization))
        return reply(401, { error: 'Não autorizado.', uncertain: false });
    try {
        const path = new URL(request.url, 'http://localhost').pathname;
        if (path === '/status' && request.method === 'GET') return reply(200, client.status());
        if (request.method !== 'POST')
            return reply(405, { error: 'Método inválido.', uncertain: false });
        if (path === '/connect') {
            void client.connect();
            return reply(202, client.status());
        }
        if (path === '/disconnect') {
            await client.disconnect();
            return reply(200, client.status());
        }
        if (path === '/send') {
            let body = '';
            for await (const bytes of request) {
                body += bytes.toString();
                if (Buffer.byteLength(body) > 12000)
                    throw Object.assign(new Error('Mensagem muito longa.'), { uncertain: false });
            }
            const payload = JSON.parse(body);
            if (
                !/^[a-f0-9-]{36}$/.test(payload.key ?? '') ||
                !/^55\d{10,11}$/.test(payload.phone ?? '') ||
                typeof payload.message !== 'string' ||
                !payload.message.trim() ||
                payload.message.length > 3000
            )
                return reply(422, { error: 'Cobrança inválida.', uncertain: false });
            return reply(200, await client.send(payload));
        }
        return reply(404, { error: 'Rota inexistente.', uncertain: false });
    } catch (error) {
        reply(409, { error: error.message, uncertain: error.uncertain !== false });
    }
});
server.requestTimeout = 90000;
server.listen(Number(process.env.WHATSAPP_BRIDGE_PORT ?? 3001), '127.0.0.1', () =>
    process.stdout.write(
        'Serviço WhatsApp iniciado em 127.0.0.1. Conecte a conta no painel da academia.\n'
    )
);
const stopScheduler = startBillingScheduler({ project, php: process.env.PHP_BINARY ?? 'php' });
if (client.hasSession()) void client.connect();
async function shutdown() {
    stopScheduler();
    await client.close();
    server.close();
    process.exit(0);
}
process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
process.on('message', (message) => {
    if (message === 'shutdown') void shutdown();
});
