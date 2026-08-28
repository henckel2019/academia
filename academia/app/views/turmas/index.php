<?php
// app/views/turmas/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Turmas</h1>
    <a href="/academia/public/turmas/novo" class="btn btn-primary">Nova Turma</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Modalidade</th>
            <th>Capacidade</th>
            <th>Dias</th>
            <th>Horário</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['nome']); ?></td>
                <td><?php echo htmlspecialchars($t['modalidade_id'] ?? ''); ?></td>
                <td><?php echo (int)$t['capacidade']; ?></td>
                <td><?php echo htmlspecialchars($t['dias_semana'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($t['horario_inicio'] ?? ''); ?> - <?php echo htmlspecialchars($t['horario_fim'] ?? ''); ?></td>
                <td>
                    <a href="/academia/public/turmas/editar/<?php echo (int)$t['id']; ?>">Editar</a>
                    <a href="/academia/public/turmas/excluir/<?php echo (int)$t['id']; ?>" onclick="return confirm('Excluir turma?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
