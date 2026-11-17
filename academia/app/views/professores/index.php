<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Equipe</span>
        <h1>Professores</h1>
        <p>Gerencie os profissionais responsáveis pelos treinos.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/professores/novo')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Novo professor</span>
        </a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>CREF</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php if (empty($professores)): ?>
                <tr><td colspan="5"><div class="empty-state"><?php echo icon('user-round'); ?><div><strong>Nenhum professor cadastrado</strong><p>Adicione um profissional à equipe.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($professores as $p): ?>
                    <tr>
                        <td><strong><?php echo e($p['nome']); ?></strong></td>
                        <td><?php echo e($p['email'] ?? '—'); ?></td>
                        <td><?php echo e($p['telefone'] ?? '—'); ?></td>
                        <td><?php echo e($p['cref'] ?? '—'); ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="table-action" href="<?php echo e(url('/professores/editar/' . (int) $p['id'])); ?>" title="Editar professor" aria-label="Editar professor">
                                    <?php echo icon('edit'); ?>
                                </a>
                                <form method="post" action="<?php echo e(url('/professores/excluir/' . (int) $p['id'])); ?>" class="inline-form" onsubmit="return confirm('Desativar professor?')">
                                    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                    <button type="submit" class="table-action table-action--danger" title="Desativar professor" aria-label="Desativar professor">
                                        <?php echo icon('archive'); ?>
                                    </button>
                                </form>
                            </div>
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
