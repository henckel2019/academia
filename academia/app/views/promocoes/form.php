<?php
// app/views/promocoes/form.php
$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <h1><?php echo $isEdit ? 'Editar Promoção' : 'Nova Promoção'; ?></h1>
</div>

<form method="POST">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo htmlspecialchars($item['nome'] ?? ''); ?>" required>
    </label>
    <label>Tipo
        <select name="tipo">
            <?php foreach (['DESCONTO_PCT','DESCONTO_VALOR','PRECO_PROMOCIONAL','REGRA_TEMPO'] as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo (($item['tipo'] ?? '') === $t ? 'selected' : ''); ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Valor
        <input type="number" step="0.01" name="valor" value="<?php echo htmlspecialchars((string)($item['valor'] ?? 0)); ?>">
    </label>
    <label>Meses início *
        <input type="number" name="meses_inicio" value="<?php echo (int)($item['meses_inicio'] ?? 13); ?>">
    </label>
    <label>Meses fim
        <input type="number" name="meses_fim" value="<?php echo htmlspecialchars((string)($item['meses_fim'] ?? '')); ?>">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/promocoes" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
