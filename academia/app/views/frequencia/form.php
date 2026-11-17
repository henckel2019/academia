<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Operação</span>
        <h1>Registrar frequência</h1>
        <p>Lance a presença do aluno com data, horário e origem.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/frequencia')); ?>"><?php echo icon('arrow-left'); ?><span>Voltar</span></a>
</div>

<form method="POST" class="form-card">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Aluno
            <select name="aluno_id" required>
                <option value="">Selecione</option>
                <?php foreach ($alunos as $a): ?>
                    <option value="<?php echo (int) $a['id']; ?>"><?php echo e($a['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Turma
            <select name="turma_id">
                <option value="">Selecione</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?php echo (int) $t['id']; ?>"><?php echo e($t['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Modalidade
            <select name="modalidade_id">
                <option value="">Selecione</option>
                <?php foreach ($modalidades as $m): ?>
                    <option value="<?php echo (int) $m['id']; ?>"><?php echo e($m['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Data
            <input type="date" name="data" value="<?php echo date('Y-m-d'); ?>">
        </label>
        <label>Hora de entrada
            <input type="time" name="hora_entrada" value="<?php echo date('H:i'); ?>">
        </label>
        <label>Hora de saída
            <input type="time" name="hora_saida">
        </label>
        <label>Origem
            <select name="origem">
                <?php foreach (['MANUAL', 'WEB', 'BIOMETRIA', 'QR_CODE'] as $o): ?>
                    <option value="<?php echo e($o); ?>"><?php echo e($o); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo icon('save'); ?><span>Salvar frequência</span></button>
        <a href="<?php echo e(url('/frequencia')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
