import { readFile, writeFile, rename } from 'node:fs/promises';
import { createHash } from 'node:crypto';

export async function createSendJournal(file) {
    let entries;
    try {
        entries = JSON.parse(await readFile(file, 'utf8'));
    } catch (error) {
        if (error.code !== 'ENOENT') throw error;
        entries = {};
    }
    async function save() {
        await writeFile(`${file}.tmp`, JSON.stringify(entries), { mode: 0o600 });
        await rename(`${file}.tmp`, file);
    }
    return {
        async send({ key, phone, message }, deliver) {
            const fingerprint = createHash('sha256')
                .update(JSON.stringify([phone, message]))
                .digest('hex');
            const previous = entries[key];
            if (previous) {
                if (previous.fingerprint !== fingerprint)
                    throw Object.assign(new Error('Cobrança alterada. Revise o envio no painel.'), {
                        uncertain: true
                    });
                if (previous.state === 'sent')
                    return { messageId: previous.messageId, replay: true };
                throw Object.assign(
                    new Error(
                        'Envio anterior sem confirmação. Confira a conversa antes de tentar novamente.'
                    ),
                    { uncertain: true }
                );
            }
            entries[key] = { fingerprint, state: 'sending', at: new Date().toISOString() };
            await save();
            const messageId = await deliver();
            if (!messageId)
                throw Object.assign(
                    new Error('WhatsApp não confirmou o identificador da mensagem.'),
                    { uncertain: true }
                );
            entries[key] = { fingerprint, state: 'sent', messageId, at: new Date().toISOString() };
            await save();
            return { messageId, replay: false };
        }
    };
}
