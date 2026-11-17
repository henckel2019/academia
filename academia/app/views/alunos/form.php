<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
$isEdit = true;
$portalPassword = '';
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Gestão acadêmica</span>
        <h1>Editar aluno</h1>
        <p>Atualize os dados, modalidades, valores e acesso ao portal.</p>
    </div>
    <a class="btn btn-secondary" href="<?php echo e(url('/alunos')); ?>">
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

<form method="post" action="<?php echo e(url('/alunos/editar/' . (int) $aluno['id'])); ?>" class="form-card student-form" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
    <?php include __DIR__ . '/_form-fields.php'; ?>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?php echo icon('save'); ?>
            <span>Salvar alterações</span>
        </button>
        <a href="<?php echo e(url('/alunos')); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
    (() => {
        const editor = document.querySelector('[data-enrollment-editor]');
        if (!editor) return;

        const list = editor.querySelector('[data-enrollment-list]');
        const template = document.querySelector('[data-enrollment-template]');
        let nextIndex = Number(list?.dataset.nextIndex || 0);
        const syncRow = (row) => {
            const modality = row.querySelector('[data-enrollment-modality]');
            row.querySelectorAll('[data-enrollment-field]').forEach((field) => { field.required = Boolean(modality?.value); });
        };
        list?.querySelectorAll('[data-enrollment-row]').forEach(syncRow);
        list?.addEventListener('change', (event) => {
            if (event.target.matches('[data-enrollment-modality]')) syncRow(event.target.closest('[data-enrollment-row]'));
        });
        list?.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-enrollment-remove]');
            if (remove) remove.closest('[data-enrollment-row]')?.remove();
        });
        editor.querySelector('[data-enrollment-add]')?.addEventListener('click', () => {
            if (!template || !list) return;
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
            syncRow(list.lastElementChild);
        });
    })();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
