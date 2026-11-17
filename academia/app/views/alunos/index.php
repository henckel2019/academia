<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
$aluno = [];
$matriculas = [];
$isEdit = false;
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Gestão acadêmica</span>
        <h1>Alunos</h1>
        <p>Cadastre alunos, matrículas, cobranças e acessos ao portal.</p>
    </div>
    <div class="page-header-actions">
        <a href="#novo-aluno" class="btn btn-primary" data-modal-open="novo-aluno">
            <?php echo icon('plus'); ?>
            <span>Novo aluno</span>
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success" role="status">
        <?php echo icon('check-circle'); ?>
        <span><?php echo e($message); ?></span>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error" role="alert">
        <?php echo icon('alert'); ?>
        <span><?php echo e($error); ?></span>
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table students-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>Matrícula</th>
                <th>Modalidades</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alunos)): ?>
                <tr><td colspan="6"><div class="empty-state"><?php echo icon('users'); ?><div><strong>Nenhum aluno cadastrado</strong><p>Comece adicionando o primeiro aluno.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($alunos as $item): ?>
                    <?php
                    $situacao = (string) ($item['situacao'] ?? 'ATIVO');
                    $enrollments = is_array($item['matriculas'] ?? null) ? $item['matriculas'] : [];
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo e($item['nome']); ?></strong>
                            <?php if (!empty($item['email'])): ?><small class="table-subtext"><?php echo e($item['email']); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo e($item['cpf'] ?? '—'); ?></td>
                        <td><?php echo e($item['matricula_codigo'] ?? '—'); ?></td>
                        <td>
                            <?php if ($enrollments === []): ?>
                                <span class="table-subtext">Sem modalidade</span>
                            <?php else: ?>
                                <div class="student-table-modalities">
                                    <?php foreach ($enrollments as $enrollment): ?>
                                        <span><?php echo e($enrollment['modalidade_nome'] ?? 'Modalidade'); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge--<?php echo e(strtolower($situacao)); ?>"><?php echo e($situacao); ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="table-action" href="<?php echo e(url('/alunos/editar/' . (int) $item['id'])); ?>" title="Editar aluno" aria-label="Editar aluno">
                                    <?php echo icon('edit'); ?>
                                </a>
                                <?php if ($situacao === 'ATIVO'): ?>
                                    <form method="post" action="<?php echo e(url('/alunos/inativar/' . (int) $item['id'])); ?>" class="inline-form" onsubmit="return confirm('Inativar este aluno?')">
                                        <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                        <button type="submit" class="table-action" title="Inativar aluno" aria-label="Inativar aluno">
                                            <?php echo icon('archive'); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?php echo e(url('/alunos/ativar/' . (int) $item['id'])); ?>" class="inline-form">
                                        <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                        <button type="submit" class="table-action table-action--success" title="Ativar aluno" aria-label="Ativar aluno">
                                            <?php echo icon('check-circle'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?php echo e(url('/alunos/excluir/' . (int) $item['id'])); ?>" class="inline-form" onsubmit="return confirm('Excluir este aluno da lista? O histórico será preservado.')">
                                    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                    <button type="submit" class="table-action table-action--danger" title="Excluir aluno" aria-label="Excluir aluno">
                                        <?php echo icon('trash'); ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="novo-aluno" data-modal data-open-on-load="<?php echo !empty($error) ? 'true' : 'false'; ?>" aria-hidden="true">
    <button class="modal-backdrop" type="button" data-modal-close aria-label="Fechar cadastro"></button>
    <section class="modal-dialog modal-dialog--wide" role="dialog" aria-modal="true" aria-labelledby="new-student-title">
        <div class="modal-header">
            <div>
                <span class="eyebrow">Novo cadastro</span>
                <h2 id="new-student-title">Cadastrar aluno</h2>
                <p>Crie o cadastro, o acesso do portal e a cobrança mensal.</p>
            </div>
            <button class="icon-button" type="button" data-modal-close aria-label="Fechar cadastro">
                <?php echo icon('close'); ?>
            </button>
        </div>
        <form method="post" action="<?php echo e(url('/alunos/novo')); ?>" class="student-form" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
            <?php $portalPassword = $portalPassword ?? '';
include __DIR__ . '/_form-fields.php'; ?>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?php echo icon('save'); ?>
                    <span>Salvar aluno</span>
                </button>
                <button type="button" class="btn btn-secondary" data-modal-close>Cancelar</button>
            </div>
        </form>
    </section>
</div>

<script>
    (() => {
        const modal = document.querySelector('[data-modal]');
        const editor = document.querySelector('[data-enrollment-editor]');
        if (!modal || !editor) return;

        const list = editor.querySelector('[data-enrollment-list]');
        const template = document.querySelector('[data-enrollment-template]');
        let nextIndex = Number(list?.dataset.nextIndex || 0);

        const syncRow = (row) => {
            const modality = row.querySelector('[data-enrollment-modality]');
            row.querySelectorAll('[data-enrollment-field]').forEach((field) => {
                field.required = Boolean(modality?.value);
            });
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
            const fragment = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            list.insertAdjacentHTML('beforeend', fragment);
            syncRow(list.lastElementChild);
        });

        const openModal = () => {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            document.querySelectorAll('.sidebar, .topbar').forEach(element => { element.inert = true; });
            modal.querySelector('input:not([type="hidden"])')?.focus();
        };
        const closeModal = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.sidebar, .topbar').forEach(element => { element.inert = element.classList.contains('sidebar') && window.innerWidth <= 900; });
            document.querySelector('[data-modal-open="novo-aluno"]')?.focus();
            if (window.location.hash === '#novo-aluno') history.replaceState(null, '', window.location.pathname + window.location.search);
        };

        document.querySelectorAll('[data-modal-open="novo-aluno"]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            window.history.replaceState(null, '', '#novo-aluno');
            openModal();
        }));
        modal.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', closeModal));
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });
        modal.addEventListener('keydown', event => {
            if (event.key !== 'Tab') return;
            const targets = [...modal.querySelectorAll('input:not([type="hidden"]), select, button, a[href]')].filter(element => !element.disabled && element.offsetParent !== null);
            if (event.shiftKey && document.activeElement === targets[0]) { event.preventDefault(); targets.at(-1).focus(); }
            else if (!event.shiftKey && document.activeElement === targets.at(-1)) { event.preventDefault(); targets[0].focus(); }
        });
        if (window.location.hash === '#novo-aluno' || modal.dataset.openOnLoad === 'true') openModal();
    })();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
