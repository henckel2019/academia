<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Administração</span>
        <h1>Usuários</h1>
        <p>Controle os acessos da equipe e os perfis de permissão.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/usuarios/novo')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Novo usuário</span>
        </a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Acesso</th></tr>
        </thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="5"><div class="empty-state"><?php echo icon('users'); ?><div><strong>Nenhum usuário cadastrado</strong><p>Adicione um acesso para a equipe.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($lista as $u): ?>
                    <tr>
                        <td><strong><?php echo e($u['nome']); ?></strong></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><span class="badge"><?php echo e($u['perfil_nome']); ?></span></td>
                        <td><span class="badge badge--<?php echo $u['ativo'] ? 'ativo' : 'inativo'; ?>"><?php echo $u['ativo'] ? 'Ativo' : 'Inativo'; ?></span></td>
                        <td><?php if ($u['primeiro_acesso']): ?><span class="badge badge--warning">Primeiro acesso</span><?php else: ?><span class="hint">Concluído</span><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
