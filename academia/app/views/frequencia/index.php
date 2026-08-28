<?php
// app/views/frequencia/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Frequência</h1>
    <a href="/academia/public/frequencia/novo" class="btn btn-primary">Registrar Frequência</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Aluno</th>
            <th>Turma</th>
            <th>Data</th>
            <th>Entrada</th>
            <th>Saída</th>
            <th>Origem</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $f): ?>
            <tr>
                <td><?php echo htmlspecialchars($f['aluno_nome']); ?></td>
                <td><?php echo htmlspecialchars($f['turma_nome'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['data']); ?></td>
                <td><?php echo htmlspecialchars($f['hora_entrada']); ?></td>
                <td><?php echo htmlspecialchars($f['hora_saida'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['origem']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
