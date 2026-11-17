<?php

declare(strict_types=1);

$content = ob_get_clean();
$isEdit = isset($item);
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Atendimento individual</span>
        <h1><?php echo $isEdit ? 'Editar aula particular' : 'Nova aula particular'; ?></h1>
        <p>Use este cadastro somente para organizar atendimentos particulares. Ele não altera os horários públicos da modalidade.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/turmas')); ?>"><?php echo icon('arrow-left'); ?><span>Voltar</span></a>
</div>

<form method="POST" class="form-card">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Identificação do atendimento *
            <input type="text" name="nome" value="<?php echo e($item['nome'] ?? ''); ?>" placeholder="Particular — nome do aluno" required>
        </label>
        <label>Modalidade
            <select name="modalidade_id" required>
                <option value="">Selecione</option>
                <?php foreach ($modalidades as $m): ?>
                    <option value="<?php echo (int) $m['id']; ?>" <?php echo (($item['modalidade_id'] ?? '') == $m['id'] ? 'selected' : ''); ?>><?php echo e($m['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Professor
            <select name="professor_id">
                <option value="">Selecione</option>
                <?php foreach ($professores as $p): ?>
                    <option value="<?php echo (int) $p['id']; ?>" <?php echo (($item['professor_id'] ?? '') == $p['id'] ? 'selected' : ''); ?>><?php echo e($p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Vagas
            <input type="number" name="capacidade" min="1" max="1" value="1" readonly>
        </label>
        <label>Dia(s) da aula
            <input type="text" name="dias_semana" value="<?php echo e($item['dias_semana'] ?? ''); ?>" placeholder="Seg / Qua / Sex">
        </label>
        <label>Horário de início
            <input type="time" name="horario_inicio" value="<?php echo e($item['horario_inicio'] ?? ''); ?>">
        </label>
        <label>Horário de fim
            <input type="time" name="horario_fim" value="<?php echo e($item['horario_fim'] ?? ''); ?>">
        </label>
        <label>Unidade
            <input type="text" name="unidade" value="<?php echo e($item['unidade'] ?? ''); ?>">
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo icon('save'); ?><span>Salvar turma</span></button>
        <a href="<?php echo e(url('/turmas')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
