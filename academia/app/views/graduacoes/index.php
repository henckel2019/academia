<?php

declare(strict_types=1);

$content = ob_get_clean();
$formatDate = static function (mixed $date): string {
    if (!$date) {
        return '—';
    }

    return (new DateTimeImmutable((string) $date))->format('d/m/Y');
};
$chargeLabels = [
    'ABERTA' => 'Em aberto',
    'VENCIDA' => 'Vencida',
    'PAGA' => 'Paga',
    'CANCELADA' => 'Cancelada',
];
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Evolução técnica</span>
        <h1>Graduações</h1>
        <p>Registre a evolução dos alunos e controle a cobrança da graduação.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/graduacoes/nova')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Nova graduação</span>
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success" role="status">
        <?php echo icon('check-circle'); ?>
        <span><?php echo e($message); ?></span>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error" role="alert">
        <?php echo icon('alert'); ?>
        <span><?php echo e($error); ?></span>
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table graduation-table">
        <thead>
            <tr><th>Aluno</th><th>Modalidade</th><th>Graduação</th><th>Data</th><th>Valor</th><th>Cobrança</th></tr>
        </thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="6"><div class="empty-state"><?php echo icon('award'); ?><div><strong>Nenhuma graduação registrada</strong><p>Registre a primeira graduação para gerar a cobrança.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($lista as $graduation): ?>
                    <?php
                    $chargeStatus = (string) ($graduation['cobranca_status'] ?? '');
                    $chargeClass = match ($chargeStatus) {
                        'PAGA' => 'success',
                        'VENCIDA' => 'danger',
                        'ABERTA' => 'warning',
                        default => 'inativo',
                    };
                    ?>
                    <tr>
                        <td><strong><?php echo e($graduation['aluno_nome']); ?></strong></td>
                        <td><?php echo e($graduation['modalidade_nome']); ?></td>
                        <td><span class="badge"><?php echo e($graduation['faixa']); ?> · grau <?php echo (int) $graduation['grau']; ?></span></td>
                        <td><?php echo e($formatDate($graduation['data_graduacao'] ?? null)); ?></td>
                        <td>R$ <?php echo e(format_money($graduation['valor'] ?? '0.00')); ?></td>
                        <td>
                            <?php if ($chargeStatus === ''): ?>
                                <span class="badge badge--inativo">Sem cobrança</span>
                            <?php else: ?>
                                <span class="badge badge--<?php echo e($chargeClass); ?>"><?php echo e($chargeLabels[$chargeStatus] ?? $chargeStatus); ?></span>
                                <small class="table-subtext">Vence <?php echo e($formatDate($graduation['cobranca_data_vencimento'] ?? null)); ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
