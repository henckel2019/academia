import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createSendJournal } from '../send-journal.mjs';

test('replays a confirmed send after restart without delivering twice', async () => {
    const directory = await mkdtemp(join(tmpdir(), 'academia-whatsapp-'));
    try {
        const file = join(directory, 'journal.json');
        const payload = {
            key: 'test-key',
            phone: '5541999990000',
            message: 'Teste sem envio externo'
        };
        let count = 0;
        const deliver = async () => {
            count += 1;
            return 'message-123';
        };
        assert.equal((await (await createSendJournal(file)).send(payload, deliver)).replay, false);
        assert.equal((await (await createSendJournal(file)).send(payload, deliver)).replay, true);
        assert.equal(count, 1);
        await assert.rejects(
            () =>
                createSendJournal(file).then((j) =>
                    j.send({ ...payload, phone: '5541888880000' }, deliver)
                ),
            /alterada/
        );
    } finally {
        await rm(directory, { recursive: true, force: true });
    }
});

test('a failed or uncertain transport is never repeated automatically', async () => {
    const directory = await mkdtemp(join(tmpdir(), 'academia-whatsapp-'));
    try {
        const file = join(directory, 'journal.json');
        const payload = {
            key: 'test-key',
            phone: '5541999990000',
            message: 'Teste sem envio externo'
        };
        const journal = await createSendJournal(file);
        await assert.rejects(
            () =>
                journal.send(payload, async () => {
                    throw new Error('timeout');
                }),
            /timeout/
        );
        let repeated = false;
        await assert.rejects(
            () =>
                createSendJournal(file).then((j) =>
                    j.send(payload, async () => {
                        repeated = true;
                        return 'duplicate';
                    })
                ),
            /sem confirmação/
        );
        assert.equal(repeated, false);
    } finally {
        await rm(directory, { recursive: true, force: true });
    }
});
