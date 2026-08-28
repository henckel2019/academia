<?php
// app/views/alunos/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Alunos</h1>
    <a href="/academia/public/alunos/novo" class="btn btn-primary">Novo Aluno</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>CPF</th>
            <th>Matrícula</th>
            <th>Situação</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                <td><?php echo htmlspecialchars($aluno['cpf'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($aluno['matricula_codigo'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($aluno['situacao'] ?? 'ATIVO'); ?></td>
                <td>
                    <a href="/academia/public/alunos/editar/<?php echo (int)$aluno['id']; ?>">Editar</a>
                    <a href="/academia/public/alunos/excluir/<?php echo (int)$aluno['id']; ?>" onclick="return confirm('Excluir aluno?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
