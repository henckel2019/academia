import { spawn } from 'node:child_process';

export function startBillingScheduler({ project, php }) {
    let running;
    const timer = setInterval(() => {
        if (running) return;
        running = spawn(php, ['bin/whatsapp-reminders.php'], {
            cwd: project,
            windowsHide: true,
            stdio: ['ignore', 'ignore', 'pipe']
        });
        running.stderr.on('data', (bytes) => process.stderr.write(bytes));
        running.on('error', () => {
            process.stderr.write('Não foi possível iniciar o processamento de cobranças PHP.\n');
        });
        running.on('close', () => {
            running = undefined;
        });
    }, 60000);
    return () => {
        clearInterval(timer);
        running?.kill();
    };
}
