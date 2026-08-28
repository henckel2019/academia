<?php
// app/views/promocoes/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Promoções</h1>
    <a href="/academia/public/promocoes/nova" class="btn btn-primary">Nova Promoção</a>
</div>

<table class="table">
    <thead>
        <tr><th>Nome</th><th>Tipo</th><th>Valor</th><th>Tempo (meses)</th><th>Ações</th></tr>
    </thead>
    <tbody>
        <?php foreach ($promocoes as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['nome']); ?></td>
                <td><?php echo htmlspecialchars($p['tipo']); ?></td>
                <td><?php echo number_format((float)$p['valor'], 2, ',', '.'); ?></td>
                <td><?php echo (int)$p['meses_inicio']; ?> - <?php echo $p['meses_fim'] ?? '+'; ?></td>
                <td>
                    <a href="/academia/public/promocoes/editar/<?php echo (int)$p['id']; ?>">Editar</a>
                    <a href="/academia/public/promocoes/excluir/<?php echo (int)$p['id']; ?>" onclick="return confirm('Excluir promoção?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
