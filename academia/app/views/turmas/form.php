<?php
// app/views/turmas/form.php
$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <h1><?php echo $isEdit ? 'Editar Turma' : 'Nova Turma'; ?></h1>
</div>

<form method="POST">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo htmlspecialchars($item['nome'] ?? ''); ?>" required>
    </label>
    <label>Modalidade
        <select name="modalidade_id" required>
            <option value="">Selecione</option>
            <?php foreach ($modalidades as $m): ?>
                <option value="<?php echo (int)$m['id']; ?>" <?php echo (($item['modalidade_id'] ?? '') == $m['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($m['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Professor
        <select name="professor_id">
            <option value="">Selecione</option>
            <?php foreach ($professores as $p): ?>
                <option value="<?php echo (int)$p['id']; ?>" <?php echo (($item['professor_id'] ?? '') == $p['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($p['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Capacidade
        <input type="number" name="capacidade" value="<?php echo (int)($item['capacidade'] ?? 0); ?>">
    </label>
    <label>Dias da semana
        <input type="text" name="dias_semana" value="<?php echo htmlspecialchars($item['dias_semana'] ?? ''); ?>">
    </label>
    <label>Horário início
        <input type="time" name="horario_inicio" value="<?php echo htmlspecialchars($item['horario_inicio'] ?? ''); ?>">
    </label>
    <label>Horário fim
        <input type="time" name="horario_fim" value="<?php echo htmlspecialchars($item['horario_fim'] ?? ''); ?>">
    </label>
    <label>Unidade
        <input type="text" name="unidade" value="<?php echo htmlspecialchars($item['unidade'] ?? ''); ?>">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/turmas" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
