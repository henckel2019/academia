<?php

declare(strict_types=1);

$content = ob_get_clean();
$isEdit = isset($item['id']);
$horarios = is_array($horarios ?? null) ? $horarios : [];
$weekdays = [
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
    7 => 'Domingo',
];
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Gestão acadêmica</span>
        <h1><?php echo $isEdit ? 'Editar modalidade' : 'Nova modalidade'; ?></h1>
        <p>Cadastre a modalidade e os horários que serão exibidos na landing page.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/modalidades')); ?>">
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

<form method="POST" class="form-card modality-form">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">

    <div class="form-grid">
        <label class="field-full">Nome da modalidade *
            <input type="text" name="nome" value="<?php echo e($item['nome'] ?? ''); ?>" maxlength="100" required autofocus>
        </label>
        <label class="field-full">Descrição
            <textarea name="descricao" maxlength="500" placeholder="Explique o foco desta modalidade"><?php echo e($item['descricao'] ?? ''); ?></textarea>
        </label>
    </div>

    <div class="schedule-editor" data-schedule-editor>
        <div class="schedule-editor__heading">
            <div>
                <span class="eyebrow">Grade semanal</span>
                <h2>Dias e horários</h2>
                <p>Adicione todos os treinos desta modalidade. A grade ativa será publicada automaticamente na landing page.</p>
            </div>
            <button class="btn btn-secondary" type="button" data-schedule-add>
                <?php echo icon('plus'); ?>
                <span>Adicionar horário</span>
            </button>
        </div>

        <div class="schedule-list" data-schedule-list data-next-index="<?php echo count($horarios); ?>">
            <?php foreach ($horarios as $index => $horario): ?>
                <div class="schedule-row" data-schedule-row>
                    <div class="schedule-row__top">
                        <strong>Horário <?php echo (int) $index + 1; ?></strong>
                        <button class="table-action table-action--danger" type="button" data-schedule-remove title="Remover horário" aria-label="Remover horário">
                            <?php echo icon('trash'); ?>
                        </button>
                    </div>
                    <div class="schedule-row__fields">
                        <label>Dia da semana
                            <select name="horarios[<?php echo (int) $index; ?>][dia_semana]" required>
                                <?php foreach ($weekdays as $day => $label): ?>
                                    <option value="<?php echo $day; ?>" <?php echo (int) ($horario['dia_semana'] ?? 0) === $day ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Início
                            <input type="time" name="horarios[<?php echo (int) $index; ?>][horario_inicio]" value="<?php echo e($horario['horario_inicio'] ?? ''); ?>" required>
                        </label>
                        <label>Fim (opcional)
                            <input type="time" name="horarios[<?php echo (int) $index; ?>][horario_fim]" value="<?php echo e($horario['horario_fim'] ?? ''); ?>">
                        </label>
                        <label class="field-full">Observação da aula (opcional)
                            <input name="horarios[<?php echo (int) $index; ?>][observacao]" maxlength="150" value="<?= e($horario['observacao'] ?? '') ?>" placeholder="Ex.: fundamentos de Boxe…">
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="schedule-empty" data-schedule-empty <?php echo $horarios === [] ? '' : 'hidden'; ?>>
            <?php echo icon('calendar'); ?>
            <span>Nenhum horário adicionado. A modalidade continuará cadastrada, mas a grade ficará como “A definir”.</span>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?php echo icon('save'); ?>
            <span>Salvar modalidade</span>
        </button>
        <a href="<?php echo e(url('/modalidades')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<template data-schedule-template>
    <div class="schedule-row" data-schedule-row>
        <div class="schedule-row__top">
            <strong>Horário</strong>
            <button class="table-action table-action--danger" type="button" data-schedule-remove title="Remover horário" aria-label="Remover horário">
                <?php echo icon('trash'); ?>
            </button>
        </div>
        <div class="schedule-row__fields">
            <label>Dia da semana
                <select name="horarios[__INDEX__][dia_semana]" required>
                    <?php foreach ($weekdays as $day => $label): ?>
                        <option value="<?php echo $day; ?>"><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Início
                <input type="time" name="horarios[__INDEX__][horario_inicio]" required>
            </label>
            <label>Fim (opcional)
                <input type="time" name="horarios[__INDEX__][horario_fim]">
            </label>
            <label class="field-full">Observação da aula (opcional)
                <input name="horarios[__INDEX__][observacao]" maxlength="150" placeholder="Ex.: fundamentos de Boxe…">
            </label>
        </div>
    </div>
</template>

<script>
    (() => {
        const editor = document.querySelector('[data-schedule-editor]');
        if (!editor) return;

        const list = editor.querySelector('[data-schedule-list]');
        const template = document.querySelector('[data-schedule-template]');
        const empty = editor.querySelector('[data-schedule-empty]');
        let nextIndex = Number(list?.dataset.nextIndex || 0);

        const syncEmptyState = () => {
            if (empty) empty.hidden = Boolean(list?.querySelector('[data-schedule-row]'));
        };

        list?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-schedule-remove]');
            if (!removeButton) return;
            removeButton.closest('[data-schedule-row]')?.remove();
            syncEmptyState();
        });

        editor.querySelector('[data-schedule-add]')?.addEventListener('click', () => {
            if (!template || !list) return;
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            list.insertAdjacentHTML('beforeend', html);
            syncEmptyState();
            list.lastElementChild?.querySelector('select')?.focus();
        });

        syncEmptyState();
    })();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
