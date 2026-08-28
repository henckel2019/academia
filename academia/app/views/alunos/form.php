<?php
// app/views/alunos/form.php
$content = ob_get_clean();
$isEdit = isset($aluno);
ob_start();
?>
<div class="page-header">
    <h1><?php echo $isEdit ? 'Editar Aluno' : 'Novo Aluno'; ?></h1>
</div>

<form method="POST" autocomplete="off">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno['nome'] ?? ''); ?>" required>
    </label>
    <label>CPF
        <input type="text" name="cpf" value="<?php echo htmlspecialchars($aluno['cpf'] ?? ''); ?>">
    </label>
    <label>Email
        <input type="email" name="email" value="<?php echo htmlspecialchars($aluno['email'] ?? ''); ?>">
    </label>
    <label>Telefone
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($aluno['telefone'] ?? ''); ?>">
    </label>
    <label>Data de nascimento
        <input type="date" name="data_nascimento" value="<?php echo htmlspecialchars($aluno['data_nascimento'] ?? ''); ?>">
    </label>
    <label>Código matrícula
        <input type="text" name="matricula_codigo" value="<?php echo htmlspecialchars($aluno['matricula_codigo'] ?? ''); ?>">
    </label>
    <label>Data de matrícula
        <input type="date" name="data_matricula" value="<?php echo htmlspecialchars($aluno['data_matricula'] ?? date('Y-m-d')); ?>">
    </label>
    <label>Situação
        <select name="situacao">
            <?php foreach (['ATIVO','SUSPENSO','CANCELADO'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo (($aluno['situacao'] ?? 'ATIVO') === $s ? 'selected' : ''); ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/alunos" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
