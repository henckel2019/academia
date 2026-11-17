<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Operação</span>
        <h1>Frequência</h1>
        <p>Registre e acompanhe a presença nos treinos.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/frequencia/novo')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Registrar frequência</span>
        </a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Aluno</th><th>Turma</th><th>Data</th><th>Entrada</th><th>Saída</th><th>Origem</th></tr>
        </thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="6"><div class="empty-state"><?php echo icon('clipboard'); ?><div><strong>Nenhuma frequência registrada</strong><p>Os registros aparecerão aqui após o primeiro check-in.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($lista as $f): ?>
                    <tr>
                        <td><strong><?php echo e($f['aluno_nome']); ?></strong></td>
                        <td><?php echo e($f['turma_nome'] ?? '—'); ?></td>
                        <td><?php echo e($f['data']); ?></td>
                        <td><?php echo e($f['hora_entrada']); ?></td>
                        <td><?php echo e($f['hora_saida'] ?? '—'); ?></td>
                        <td><span class="badge"><?php echo e($f['origem']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
