<?php

declare(strict_types=1);

$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Equipe</span>
        <h1><?php echo $isEdit ? 'Editar professor' : 'Novo professor'; ?></h1>
        <p>Mantenha os dados dos profissionais responsáveis pelos treinos.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/professores')); ?>"><?php echo icon('arrow-left'); ?><span>Voltar</span></a>
</div>

<form method="POST" class="form-card">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Nome *
            <input type="text" name="nome" value="<?php echo e($item['nome'] ?? ''); ?>" required>
        </label>
        <label>E-mail
            <input type="email" name="email" value="<?php echo e($item['email'] ?? ''); ?>">
        </label>
        <label>Telefone
            <input type="text" name="telefone" value="<?php echo e($item['telefone'] ?? ''); ?>">
        </label>
        <label>CREF
            <input type="text" name="cref" value="<?php echo e($item['cref'] ?? ''); ?>">
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo icon('save'); ?><span>Salvar professor</span></button>
        <a href="<?php echo e(url('/professores')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
