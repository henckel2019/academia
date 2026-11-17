<?php

declare(strict_types=1);

$content = ob_get_clean();
$item = is_array($item ?? null) ? $item : [];
$graduationDate = (string) ($item['data_graduacao'] ?? date('Y-m-d'));
$dueDate = (string) ($item['data_vencimento'] ?? date('Y-m-d'));
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Evolução técnica</span>
        <h1>Nova graduação</h1>
        <p>Registre a conquista e gere automaticamente a cobrança avulsa do aluno.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/graduacoes')); ?>">
        <?php echo icon('arrow-left'); ?>
        <span>Voltar</span>
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error" role="alert">
        <?php echo icon('alert'); ?>
        <span><?php echo e($error); ?></span>
    </div>
<?php endif; ?>

<form method="POST" class="form-card graduation-form">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <div class="form-grid">
        <label>Aluno *
            <select name="aluno_id" required>
                <option value="">Selecione o aluno</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo (int) $aluno['id']; ?>" <?php echo (int) ($item['aluno_id'] ?? 0) === (int) $aluno['id'] ? 'selected' : ''; ?>><?php echo e($aluno['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Modalidade *
            <select name="modalidade_id" required>
                <option value="">Selecione a modalidade</option>
                <?php foreach ($modalidades as $modalidade): ?>
                    <option value="<?php echo (int) $modalidade['id']; ?>" <?php echo (int) ($item['modalidade_id'] ?? 0) === (int) $modalidade['id'] ? 'selected' : ''; ?>><?php echo e($modalidade['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Faixa ou graduação *
            <input type="text" name="faixa" value="<?php echo e($item['faixa'] ?? ''); ?>" maxlength="50" placeholder="Ex.: Faixa azul" required>
        </label>
        <label>Grau
            <input type="number" name="grau" min="0" value="<?php echo e($item['grau'] ?? '0'); ?>">
        </label>
        <label>Data da graduação *
            <input type="date" name="data_graduacao" value="<?php echo e($graduationDate); ?>" required>
        </label>
        <label>Professor responsável
            <select name="professor_id">
                <option value="">Selecione</option>
                <?php foreach ($professores as $professor): ?>
                    <option value="<?php echo (int) $professor['id']; ?>" <?php echo (int) ($item['professor_id'] ?? 0) === (int) $professor['id'] ? 'selected' : ''; ?>><?php echo e($professor['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Valor da graduação *
            <input type="text" name="valor" value="<?php echo e($item['valor'] ?? ''); ?>" inputmode="decimal" placeholder="150,00" required>
        </label>
        <label>Vencimento da cobrança *
            <input type="date" name="data_vencimento" value="<?php echo e($dueDate); ?>" required>
        </label>
        <label class="field-full">Observações
            <textarea name="observacoes" maxlength="1000" placeholder="Informações adicionais sobre a graduação"><?php echo e($item['observacoes'] ?? ''); ?></textarea>
        </label>
    </div>

    <div class="graduation-charge-note">
        <?php echo icon('wallet'); ?>
        <div>
            <strong>Uma cobrança será criada automaticamente</strong>
            <p>O valor informado ficará como cobrança avulsa de graduação, vinculada à matrícula do aluno e visível no financeiro e no portal.</p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?php echo icon('save'); ?>
            <span>Registrar e gerar cobrança</span>
        </button>
        <a href="<?php echo e(url('/graduacoes')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
