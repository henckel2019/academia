<?php

declare(strict_types=1);

$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Atendimento individual</span>
        <h1>Aulas particulares</h1>
        <p>Organize somente os atendimentos particulares da academia.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/turmas/novo')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Nova aula particular</span>
        </a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Atendimento</th><th>Modalidade</th><th>Capacidade</th><th>Dias</th><th>Horário</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="6"><div class="empty-state"><?php echo icon('calendar'); ?><div><strong>Nenhuma aula particular cadastrada</strong><p>Adicione um atendimento individual para começar.</p></div></div></td></tr>
            <?php else: ?>
                <?php foreach ($lista as $t): ?>
                    <tr>
                        <td><strong><?php echo e($t['nome']); ?></strong></td>
                        <td><?php echo e($t['modalidade_id'] ?? '—'); ?></td>
                        <td><?php echo (int) $t['capacidade']; ?> vagas</td>
                        <td><?php echo e($t['dias_semana'] ?? '—'); ?></td>
                        <td><?php echo e($t['horario_inicio'] ?? '—'); ?> — <?php echo e($t['horario_fim'] ?? '—'); ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="table-action" href="<?php echo e(url('/turmas/editar/' . (int) $t['id'])); ?>" title="Editar turma" aria-label="Editar turma">
                                    <?php echo icon('edit'); ?>
                                </a>
                                <form method="post" action="<?php echo e(url('/turmas/excluir/' . (int) $t['id'])); ?>" class="inline-form" onsubmit="return confirm('Desativar turma?')">
                                    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                                    <button type="submit" class="table-action table-action--danger" title="Desativar turma" aria-label="Desativar turma">
                                        <?php echo icon('archive'); ?>
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
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
