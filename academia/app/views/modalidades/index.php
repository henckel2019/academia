<?php
// app/views/modalidades/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Modalidades</h1>
    <a href="/academia/public/modalidades/novo" class="btn btn-primary">Nova Modalidade</a>
</div>

<table class="table">
    <thead>
        <tr><th>Nome</th><th>Descrição</th><th>Ações</th></tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                <td><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></td>
                <td>
                    <a href="/academia/public/modalidades/editar/<?php echo (int)$item['id']; ?>">Editar</a>
                    <a href="/academia/public/modalidades/excluir/<?php echo (int)$item['id']; ?>" onclick="return confirm('Excluir modalidade?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
