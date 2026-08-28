<?php
// app/views/professores/form.php
$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <h1><?php echo $isEdit ? 'Editar Professor' : 'Novo Professor'; ?></h1>
</div>

<form method="POST">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo htmlspecialchars($item['nome'] ?? ''); ?>" required>
    </label>
    <label>Email
        <input type="email" name="email" value="<?php echo htmlspecialchars($item['email'] ?? ''); ?>">
    </label>
    <label>Telefone
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($item['telefone'] ?? ''); ?>">
    </label>
    <label>CREF
        <input type="text" name="cref" value="<?php echo htmlspecialchars($item['cref'] ?? ''); ?>">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/professores" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
