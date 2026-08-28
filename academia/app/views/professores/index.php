<?php
// app/views/professores/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Professores</h1>
    <a href="/academia/public/professores/novo" class="btn btn-primary">Novo Professor</a>
</div>

<table class="table">
    <thead>
        <tr><th>Nome</th><th>Email</th><th>Telefone</th><th>CREF</th><th>Ações</th></tr>
    </thead>
    <tbody>
        <?php foreach ($professores as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['nome']); ?></td>
                <td><?php echo htmlspecialchars($p['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['telefone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['cref'] ?? ''); ?></td>
                <td>
                    <a href="/academia/public/professores/editar/<?php echo (int)$p['id']; ?>">Editar</a>
                    <a href="/academia/public/professores/excluir/<?php echo (int)$p['id']; ?>" onclick="return confirm('Excluir professor?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
