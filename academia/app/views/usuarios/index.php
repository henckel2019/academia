<?php
// app/views/usuarios/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Usuários</h1>
    <a href="/academia/public/usuarios/novo" class="btn btn-primary">Novo Usuário</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Perfil</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['nome']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['perfil_nome']); ?></td>
                <td><?php echo $u['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                <td><?php if ($u['primeiro_acesso']): ?><small>Primeiro acesso</small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
