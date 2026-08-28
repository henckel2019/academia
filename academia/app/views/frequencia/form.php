<?php
// app/views/frequencia/form.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Registrar Frequência</h1>
</div>

<form method="POST">
    <label>Aluno
        <select name="aluno_id" required>
            <option value="">Selecione</option>
            <?php foreach ($alunos as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Turma
        <select name="turma_id">
            <option value="">Selecione</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Modalidade
        <select name="modalidade_id">
            <option value="">Selecione</option>
            <?php foreach ($modalidades as $m): ?>
                <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Data
        <input type="date" name="data" value="<?php echo date('Y-m-d'); ?>">
    </label>
    <label>Hora entrada
        <input type="time" name="hora_entrada" value="<?php echo date('H:i'); ?>">
    </label>
    <label>Hora saída
        <input type="time" name="hora_saida">
    </label>
    <label>Origem
        <select name="origem">
            <?php foreach (['MANUAL','BIOMETRIA','APP'] as $o): ?>
                <option value="<?php echo $o; ?>"><?php echo $o; ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/frequencia" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
