<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Administração</span>
        <h1>Promoções</h1>
        <p>Configure condições especiais para os planos da academia.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/promocoes/nova')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Nova promoção</span>
        </a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nome</th><th>Tipo</th><th>Valor</th><th>Período</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php if (empty($promocoes)): ?>
                <tr><td colspan="5"><div class="empty-state"><?php echo icon('tag'); ?><div><strong>Nenhuma promoção cadastrada</strong><p>Crie a primeira condição especial.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($promocoes as $p): ?>
                    <tr>
                        <td><strong><?php echo e($p['nome']); ?></strong></td>
                        <td><span class="badge"><?php echo e($p['tipo']); ?></span></td>
                        <td>R$ <?php echo number_format((float) $p['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo (int) $p['meses_inicio']; ?> — <?php echo $p['meses_fim'] ?? 'sem limite'; ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="table-action" href="<?php echo e(url('/promocoes/editar/' . (int) $p['id'])); ?>" title="Editar promoção" aria-label="Editar promoção">
                                    <?php echo icon('edit'); ?>
                                </a>
                                <form method="post" action="<?php echo e(url('/promocoes/excluir/' . (int) $p['id'])); ?>" class="inline-form" onsubmit="return confirm('Desativar promoção?')">
                                    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                    <button type="submit" class="table-action table-action--danger" title="Desativar promoção" aria-label="Desativar promoção">
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
