<?php

declare(strict_types=1);

$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Administração</span>
        <h1><?php echo $isEdit ? 'Editar promoção' : 'Nova promoção'; ?></h1>
        <p>Defina as condições especiais aplicadas aos planos.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/promocoes')); ?>"><?php echo icon('arrow-left'); ?><span>Voltar</span></a>
</div>

<form method="POST" class="form-card">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Nome *
            <input type="text" name="nome" value="<?php echo e($item['nome'] ?? ''); ?>" required>
        </label>
        <label>Tipo
            <select name="tipo">
                <?php foreach (['DESCONTO_PCT', 'DESCONTO_VALOR', 'PRECO_PROMOCIONAL', 'REGRA_TEMPO'] as $t): ?>
                    <option value="<?php echo e($t); ?>" <?php echo (($item['tipo'] ?? '') === $t ? 'selected' : ''); ?>><?php echo e($t); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Valor
            <input type="number" step="0.01" name="valor" min="0" value="<?php echo e((string) ($item['valor'] ?? 0)); ?>">
        </label>
        <label>Meses de início *
            <input type="number" name="meses_inicio" min="0" value="<?php echo (int) ($item['meses_inicio'] ?? 13); ?>">
        </label>
        <label>Meses de fim
            <input type="number" name="meses_fim" min="0" value="<?php echo e((string) ($item['meses_fim'] ?? '')); ?>">
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo icon('save'); ?><span>Salvar promoção</span></button>
        <a href="<?php echo e(url('/promocoes')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
