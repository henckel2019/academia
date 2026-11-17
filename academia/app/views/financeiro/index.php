<?php declare(strict_types=1);
ob_start(); ?>
<div class="page-header">
    <div><span class="eyebrow">Rotina da academia</span>
        <h1>Mensalidades e pagamentos</h1>
        <p>Confirme o recebimento para atualizar a situação financeira do aluno.</p>
    </div>
</div>
<?php if ($notice): ?><div class="panel access-allowed" role="status"><?= e($notice) ?></div><?php endif ?>
<?php if ($error): ?><div class="panel access-blocked" role="alert"><?= e($error) ?></div><?php endif ?>
<section class="panel">
    <form method="get" class="finance-filters">
        <label for="mes">Competência<input id="mes" type="month" name="mes" value="<?= e($month) ?>" required></label>
        <label for="busca">Aluno ou matrícula<input id="busca" name="busca" value="<?= e($search) ?>"></label>
        <button class="btn btn-secondary" type="submit">Filtrar cobranças</button>
    </form>
    <div class="finance-toolbar">
        <p>A geração usa as matrículas ativas do mês. Você pode repetir sem duplicar cobranças.</p>
        <form method="post" class="form-actions">
            <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input type="hidden"
                name="acao" value="gerar">
            <button class="btn btn-primary" type="submit">Gerar mensalidades de <?= e($month) ?></button>
        </form>
    </div>
</section>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Aluno / modalidade</th>
                <th>Cobrança</th>
                <th>Vencimento</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Recebimento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bills as $bill): ?><tr>
                <td><strong><?= e($bill['aluno_nome']) ?></strong><br><?= e($bill['matricula_codigo']) ?> ·
                    <?= e($bill['modalidade_nome']) ?></td>
                <td><?= e($bill['descricao'] ?: $bill['tipo']) ?></td>
                <td><?= e($bill['data_vencimento']) ?></td>
                <td>R$ <?= e(format_money($bill['valor_final'])) ?></td>
                <td><span class="badge <?= match ($bill['situacao_exibicao']) {
    'PAGA' => 'badge--success', 'VENCIDA' => 'badge--danger', 'ABERTA' => 'badge--warning', default => ''
} ?>"><?= e($bill['situacao_exibicao']) ?></span></td>
                <td>
                    <?php if (in_array($bill['status'], ['ABERTA', 'VENCIDA'], true)): ?>
                    <form method="post" class="payment-form"
                        onsubmit="return confirm('Confirma que o valor integral desta cobrança foi recebido?')">
                        <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>"><input
                            type="hidden" name="acao" value="pagar"><input type="hidden" name="mensalidade_id"
                            value="<?= e($bill['id']) ?>">
                        <label>Forma de pagamento<select name="tipo" required>
                                <option value="">Selecione</option>
                                <option value="PIX">PIX recebido</option>
                                <option value="DINHEIRO">Dinheiro</option>
                                <option value="CARTAO">Cartão</option>
                                <option value="BOLETO">Boleto</option>
                                <option value="OUTRO">Outro</option>
                            </select></label><button class="btn btn-primary" type="submit">Confirmar
                            recebimento</button>
                    </form><?php else: ?><?= e($bill['data_pagamento'] ?? '—') ?><?php endif ?>
                </td>
            </tr><?php endforeach ?>
            <?php if (!$bills): ?><tr>
                <td colspan="6">
                    <div class="empty-state"><?= icon('wallet') ?><div><strong>Nenhuma cobrança por aqui.</strong>
                            <p>Escolha o mês e gere as mensalidades para começar a acompanhar os recebimentos.</p>
                        </div>
                    </div>
                </td>
            </tr><?php endif ?>
        </tbody>
    </table>
</div>
<p class="hint">Baixa manual de valores efetivamente recebidos. A confirmação automática de PIX depende de um provedor
    bancário.</p>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/main.php'; ?>
