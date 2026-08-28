<?php
// app/views/graduacoes/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Graduações</h1>
    <a href="/academia/public/graduacoes/nova" class="btn btn-primary">Nova Graduação</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Aluno</th>
            <th>Modalidade</th>
            <th>Faixa</th>
            <th>Grau</th>
            <th>Data</th>
            <th>Professor</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $g): ?>
            <tr>
                <td><?php echo htmlspecialchars($g['aluno_nome']); ?></td>
                <td><?php echo htmlspecialchars($g['modalidade_nome']); ?></td>
                <td><?php echo htmlspecialchars($g['faixa']); ?></td>
                <td><?php echo (int)$g['grau']; ?></td>
                <td><?php echo htmlspecialchars($g['data_graduacao']); ?></td>
                <td><?php echo htmlspecialchars($g['professor_responsavel'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
