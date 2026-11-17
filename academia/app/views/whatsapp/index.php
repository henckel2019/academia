<?php declare(strict_types=1);
ob_start(); ?>
<div class="page-header">
    <div><span class="eyebrow">Contato com os alunos</span>
        <h1>WhatsApp e cobranças</h1>
        <p>Conecte o número da academia e envie um lembrete mensal no vencimento de cada aluno.</p>
    </div>
</div>
<?php if ($notice): ?><div class="alert alert-success" role="status"><?= e($notice) ?></div><?php endif ?>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif ?>
<div class="checkin-layout">
    <section class="panel" data-whatsapp data-status-url="<?= e(url('/whatsapp/status')) ?>">
        <div class="panel-heading">
            <div>
                <h2>Conectar WhatsApp Web</h2>
                <p>Use o celular que tem o WhatsApp da academia.</p>
            </div><span class="badge" data-whatsapp-state>Verificando…</span>
        </div>
        <p data-whatsapp-status role="status" aria-live="polite">Consultando a conexão…</p>
        <img class="whatsapp-qr" data-whatsapp-qr width="300" height="300"
            alt="QR Code para conectar o WhatsApp da academia" hidden>
        <ol class="connection-steps">
            <li>No WhatsApp do celular, abra <strong>Aparelhos conectados</strong>.</li>
            <li>Toque em <strong>Conectar um aparelho</strong>.</li>
            <li>Leia o QR Code que aparece nesta tela.</li>
        </ol>
        <div class="form-actions">
            <form method="post"><input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input
                    type="hidden" name="acao" value="conectar"><button class="btn btn-primary" type="submit">Conectar /
                    gerar QR Code</button></form>
            <form method="post"
                onsubmit="return confirm('Desconectar o WhatsApp da academia e interromper os envios até uma nova conexão?')">
                <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input type="hidden"
                    name="acao" value="desconectar"><button class="btn btn-secondary" type="submit">Desconectar
                    conta</button></form>
        </div>
        <p class="hint">Mantenha este computador ou servidor ligado e conectado à internet. A conta permanece conectada
            entre reinícios, até ser desconectada no WhatsApp.</p>
    </section>
    <section class="panel">
        <h2>Cobrança mensal automática</h2>
        <form method="post" class="whatsapp-settings">
            <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input type="hidden"
                name="acao" value="salvar">
            <label class="consent-label"><input type="checkbox" name="ativa"
                    <?= !empty($settings['whatsapp_cobranca_ativa']) ? 'checked' : '' ?>> Ativar envio mensal de
                cobranças</label>
            <label>Quando lembrar o aluno<select
                    name="dias_antecedencia"><?php foreach ([0 => 'No dia do vencimento', 1 => '1 dia antes', 2 => '2 dias antes', 3 => '3 dias antes', 5 => '5 dias antes', 10 => '10 dias antes'] as $days => $label): ?>
                    <option value="<?= $days ?>"
                        <?= (int) ($settings['whatsapp_dias_antecedencia'] ?? 0) === $days ? 'selected' : '' ?>>
                        <?= e($label) ?></option><?php endforeach ?></select></label>
            <button class="btn btn-primary" type="submit">Salvar automação</button>
        </form>
        <p class="hint">Cada mensalidade recebe um lembrete. Cobranças pagas, canceladas ou de alunos sem a opção de
            WhatsApp são ignoradas. Os envios acontecem entre 9h e 20h.</p>
        <form method="post"><input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input
                type="hidden" name="acao" value="preparar"><button class="btn btn-secondary" type="submit">Preparar
                cobranças deste mês</button></form>
    </section>
</div>
<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Histórico de cobranças</h2>
            <p>Um envio sem confirmação fica em revisão para evitar mensagens duplicadas.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Competência / vencimento</th>
                    <th>WhatsApp</th>
                    <th>Situação</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?><tr>
                    <td><?= e($row['aluno_nome']) ?></td>
                    <td><?= e($row['competencia']) ?><br><?= e($row['data_vencimento']) ?></td>
                    <td><?= e($row['telefone'] ?? 'A definir no envio') ?></td>
                    <td><span
                            class="badge <?= $row['status'] === 'ENVIADA' ? 'badge--success' : '' ?>"><?= e($row['status']) ?></span>
                    </td>
                    <td><?= e($row['erro'] ?? $row['enviada_em'] ?? '') ?>
                        <?php if (in_array($row['status'], ['FALHA', 'REVISAO'], true)): ?><form method="post"
                            onsubmit="return confirm('Confira antes a conversa no WhatsApp. Uma tentativa sem confirmação pode já ter chegado ao aluno. Confirma que deve tentar novamente?')">
                            <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input
                                type="hidden" name="acao" value="repetir"><input type="hidden" name="id"
                                value="<?= e($row['id']) ?>"><button class="btn btn-secondary" type="submit">Reprocessar
                                após conferir</button></form><?php endif ?></td>
                </tr><?php endforeach ?>
                <?php if (!$history): ?><tr>
                    <td colspan="5">
                        <div class="empty-state"><?= icon('wallet') ?><div><strong>Nenhuma cobrança enviada
                                    ainda.</strong>
                                <p>Conecte a conta, ative a automação e marque WhatsApp no cadastro dos alunos.</p>
                            </div>
                        </div>
                    </td>
                </tr><?php endif ?>
            </tbody>
        </table>
    </div>
</section>
<script defer src="<?= e(asset('/js/whatsapp.js')) ?>"></script>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/main.php'; ?>
