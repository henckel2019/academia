import { spawn } from 'node:child_process';
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const project = fileURLToPath(new URL('../', import.meta.url));
const php = process.argv[2] ?? 'php';
const port = process.argv[3] ?? '8080';
const children = [];
let stopping = false;
function launch(command, args) {
    const child = spawn(command, args, {
        cwd: project,
        windowsHide: true,
        stdio: command === process.execPath ? ['inherit', 'inherit', 'inherit', 'ipc'] : 'inherit'
    });
    children.push(child);
    child.on('error', (error) => {
        process.stderr.write(`Falha ao iniciar ${command}: ${error.message}\n`);
        shutdown(1);
    });
    child.on('exit', (code) => {
        if (!stopping) shutdown(code ?? 1);
    });
    return child;
}
async function bridgeAlreadyRunning() {
    let token;
    try {
        token = (
            await readFile(new URL('../storage/whatsapp/bridge-token', import.meta.url), 'utf8')
        ).trim();
    } catch (error) {
        if (error.code === 'ENOENT') return false;
        throw error;
    }
    try {
        const response = await fetch('http://127.0.0.1:3001/status', {
            headers: { Authorization: `Bearer ${token}` },
            signal: AbortSignal.timeout(2000)
        });
        if (!response.ok) throw new Error('A porta 3001 está ocupada por outro serviço.');
        return true;
    } catch (error) {
        if (error.cause?.code === 'ECONNREFUSED') return false;
        throw error;
    }
}
function shutdown(code = 0) {
    if (stopping) return;
    stopping = true;
    children.forEach((child) => (child.connected ? child.send('shutdown') : child.kill()));
    const deadline = setTimeout(() => {
        children.forEach((child) => {
            if (child.exitCode === null) child.kill();
        });
        process.exit(code);
    }, 10000);
    Promise.all(
        children.map((child) =>
            child.exitCode !== null
                ? Promise.resolve()
                : new Promise((resolve) => child.once('exit', resolve))
        )
    ).then(() => {
        clearTimeout(deadline);
        process.exit(code);
    });
}
process.env.PHP_BINARY = php;
if (!(await bridgeAlreadyRunning())) launch(process.execPath, ['integrations/whatsapp/server.mjs']);
else process.stdout.write('Serviço WhatsApp já está iniciado.\n');
launch(php, ['-S', `0.0.0.0:${port}`, '-t', 'public', 'public/router.php']);
process.on('SIGINT', () => shutdown());
process.on('SIGTERM', () => shutdown());
