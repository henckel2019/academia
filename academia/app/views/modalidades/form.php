<?php
// app/views/modalidades/form.php
$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <h1><?php echo $isEdit ? 'Editar Modalidade' : 'Nova Modalidade'; ?></h1>
</div>

<form method="POST">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo htmlspecialchars($item['nome'] ?? ''); ?>" required>
    </label>
    <label>Descrição
        <textarea name="descricao"><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></textarea>
    </label>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/modalidades" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
