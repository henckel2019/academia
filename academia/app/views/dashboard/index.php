<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
$dashboardUser = (string) ($_SESSION['auth']['name'] ?? 'equipe');
$formatDate = static function (mixed $date): string {
    if (!$date) {
        return '—';
    }

    return (new DateTimeImmutable((string) $date))->format('d/m/Y');
};
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Visão geral · <?php echo e($competenciaLabel); ?></span>
        <h1>Olá, <?php echo e($dashboardUser); ?>.</h1>
        <p>Acompanhe a operação e a saúde financeira da academia neste mês.</p>
    </div>
</div>

<?php if (in_array($_SESSION['auth']['role'] ?? '', ['SUPER_ADMIN', 'ADMIN', 'RECEPTION'], true)): ?>
<section class="dashboard-hero" aria-labelledby="reception-title">
    <div><span class="eyebrow">Recepção pronta</span><h2 id="reception-title">O próximo treino começa na entrada.</h2><p>Consulte a situação do aluno e registre a presença em um só lugar.</p></div>
    <a class="btn btn-primary" href="<?= e(url('/checkin')) ?>"><?= icon('qr') ?> Registrar presença</a>
</section>
<?php endif ?>

<div class="cards-grid" aria-label="Resumo da academia">
    <article class="card">
        <span class="card-icon"><?php echo icon('users'); ?></span>
        <div class="card-title">Alunos ativos</div>
        <div class="card-number"><?php echo (int) ($stats['alunos_ativos'] ?? 0); ?></div>
        <div class="card-meta"><span class="status-dot"></span><span>Base atual</span></div>
    </article>
    <article class="card">
        <span class="card-icon"><?php echo icon('calendar'); ?></span>
        <div class="card-title">Aulas particulares</div>
        <div class="card-number"><?php echo (int) ($stats['turmas_ativas'] ?? 0); ?></div>
        <div class="card-meta"><span>Atendimentos individuais</span></div>
    </article>
    <article class="card card--success">
        <span class="card-icon"><?php echo icon('check-circle'); ?></span>
        <div class="card-title">Mensalidades pagas</div>
        <div class="card-number"><?php echo (int) ($stats['mensalidades_pagas'] ?? 0); ?></div>
        <div class="card-meta card-meta--success"><span>Competência <?php echo e($competencia); ?></span></div>
    </article>
    <article class="card card--danger">
        <span class="card-icon"><?php echo icon('alert'); ?></span>
        <div class="card-title">Mensalidades vencidas</div>
        <div class="card-number"><?php echo (int) ($stats['mensalidades_vencidas'] ?? 0); ?></div>
        <div class="card-meta card-meta--alert"><span>Acompanhar agora</span></div>
    </article>
</div>

<section class="panel" aria-labelledby="finance-summary-title">
    <div class="panel-heading">
        <div>
            <h2 id="finance-summary-title">Resumo financeiro</h2>
            <p>Valores da competência atual e previsão baseada nas matrículas ativas.</p>
        </div>
        <?php echo icon('wallet'); ?>
    </div>
    <div class="dashboard-financial-grid">
        <div class="metric">
            <span>Faturado em <?php echo e($competenciaLabel); ?></span>
            <strong>R$ <?php echo e(format_money($financeiro['faturamento_mes'] ?? '0.00')); ?></strong>
        </div>
        <div class="metric">
            <span>Recebido no mês</span>
            <strong>R$ <?php echo e(format_money($financeiro['recebido_mes'] ?? '0.00')); ?></strong>
        </div>
        <div class="metric metric--forecast">
            <span>Previsão para <?php echo e($proximaCompetenciaLabel); ?></span>
            <strong>R$ <?php echo e(format_money($financeiro['previsao_proximo_mes'] ?? '0.00')); ?></strong>
        </div>
    </div>
</section>

<div class="dashboard-columns">
    <section class="panel" aria-labelledby="paid-invoices-title">
        <div class="panel-heading">
            <div>
                <h2 id="paid-invoices-title">Quem já pagou</h2>
                <p>Mensalidades pagas em <?php echo e($competenciaLabel); ?>.</p>
            </div>
            <span class="badge badge--success"><?php echo (int) ($stats['mensalidades_pagas'] ?? 0); ?> pagas</span>
        </div>
        <div class="table-wrap">
            <table class="table dashboard-table">
                <thead>
                    <tr><th>Aluno</th><th>Modalidade</th><th>Valor</th><th>Pagamento</th></tr>
                </thead>
                <tbody>
                <?php if (empty($mensalidadesPagas)): ?>
                    <tr><td colspan="4"><div class="empty-state"><?php echo icon('check-circle'); ?><div><strong>Nenhum pagamento registrado</strong><p>Os pagamentos desta competência aparecerão aqui.</p></div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($mensalidadesPagas as $invoice): ?>
                        <tr>
                            <td><strong><?php echo e($invoice['aluno_nome'] ?? 'Aluno'); ?></strong></td>
                            <td><?php echo e($invoice['modalidade_nome'] ?? '—'); ?></td>
                            <td>R$ <?php echo e(format_money($invoice['valor_final'] ?? '0.00')); ?></td>
                            <td><?php echo e($formatDate($invoice['data_pagamento'] ?? null)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" aria-labelledby="overdue-invoices-title">
        <div class="panel-heading">
            <div>
                <h2 id="overdue-invoices-title">Quem está vencido</h2>
                <p>Mensalidades vencidas em <?php echo e($competenciaLabel); ?>.</p>
            </div>
            <span class="badge badge--danger"><?php echo (int) ($stats['mensalidades_vencidas'] ?? 0); ?> vencidas</span>
        </div>
        <div class="table-wrap">
            <table class="table dashboard-table">
                <thead>
                    <tr><th>Aluno</th><th>Modalidade</th><th>Valor</th><th>Vencimento</th></tr>
                </thead>
                <tbody>
                <?php if (empty($mensalidadesVencidas)): ?>
                    <tr><td colspan="4"><div class="empty-state"><?php echo icon('check-circle'); ?><div><strong>Nenhuma mensalidade vencida</strong><p>A situação financeira está em dia nesta competência.</p></div></div></td></tr>
                <?php else: ?>
                    <?php foreach ($mensalidadesVencidas as $invoice): ?>
                        <tr>
                            <td><strong><?php echo e($invoice['aluno_nome'] ?? 'Aluno'); ?></strong></td>
                            <td><?php echo e($invoice['modalidade_nome'] ?? '—'); ?></td>
                            <td>R$ <?php echo e(format_money($invoice['valor_final'] ?? '0.00')); ?></td>
                            <td><?php echo e($formatDate($invoice['data_vencimento'] ?? null)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
