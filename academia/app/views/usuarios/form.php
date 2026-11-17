<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Administração</span>
        <h1>Novo usuário</h1>
        <p>Crie um acesso com o perfil correto para cada membro da equipe.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/usuarios')); ?>"><?php echo icon('arrow-left'); ?><span>Voltar</span></a>
</div>

<form method="POST" class="form-card" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Nome *
            <input type="text" name="nome" required>
        </label>
        <label>E-mail *
            <input type="email" name="email" required>
        </label>
        <label>Senha inicial *
            <input type="password" name="senha" minlength="12" autocomplete="new-password" required>
            <span class="form-help">Use uma senha provisória com pelo menos 12 caracteres.</span>
        </label>
        <label>Perfil
            <select name="perfil_id" required>
                <option value="">Selecione</option>
                <?php foreach ($perfis as $p): ?>
                    <option value="<?php echo (int) $p['id']; ?>"><?php echo e($p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo icon('save'); ?><span>Salvar usuário</span></button>
        <a href="<?php echo e(url('/usuarios')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
