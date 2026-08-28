<?php
// app/views/graduacoes/form.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Nova Graduação</h1>
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
    <label>Modalidade
        <select name="modalidade_id" required>
            <option value="">Selecione</option>
            <?php foreach ($modalidades as $m): ?>
                <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Faixa *
        <input type="text" name="faixa" required>
    </label>
    <label>Grau
        <input type="number" name="grau" value="0">
    </label>
    <label>Data graduação
        <input type="date" name="data_graduacao" value="<?php echo date('Y-m-d'); ?>">
    </label>
    <label>Professor responsável
        <input type="text" name="professor_responsavel">
    </label>
    <label>Observações
        <textarea name="observacoes"></textarea>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/graduacoes" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
