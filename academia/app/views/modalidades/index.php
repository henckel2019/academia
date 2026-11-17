<?php

declare(strict_types=1);

$content = ob_get_clean();
$weekdays = [1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb', 7 => 'Dom'];
$formatSchedule = static function (array $schedules) use ($weekdays): string {
    if ($schedules === []) {
        return 'A definir';
    }

    $days = [];
    foreach ($schedules as $schedule) {
        $day = (int) ($schedule['dia_semana'] ?? 0);
        $time = trim((string) ($schedule['horario_inicio'] ?? ''));
        $end = trim((string) ($schedule['horario_fim'] ?? ''));
        if (!isset($weekdays[$day])) {
            continue;
        }
        $days[$day][] = $time . ($end !== '' ? '–' . $end : '');
    }

    $parts = [];
    foreach ($days as $day => $times) {
        $parts[] = $weekdays[$day] . ' ' . implode(', ', $times);
    }

    return implode(' · ', $parts) ?: 'A definir';
};
ob_start();
?>
<div class="page-header">
    <div>
        <span class="eyebrow">Gestão acadêmica</span>
        <h1>Modalidades</h1>
        <p>Cadastre as práticas da academia e publique a grade semanal na landing page.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo e(url('/modalidades/novo')); ?>" class="btn btn-primary">
            <?php echo icon('plus'); ?>
            <span>Nova modalidade</span>
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

<div class="modality-grid">
    <?php if (empty($lista)): ?>
        <div class="panel empty-state">
            <?php echo icon('dumbbell'); ?>
            <div><strong>Nenhuma modalidade cadastrada</strong><p>Adicione a primeira modalidade e seus horários.</p></div>
        </div>
    <?php else: ?>
        <?php foreach ($lista as $item): ?>
            <?php
            $schedules = is_array($item['horarios'] ?? null) ? $item['horarios'] : [];
            $active = (bool) ($item['ativo'] ?? false);
            ?>
            <article class="modality-card<?php echo $active ? '' : ' modality-card--inactive'; ?>">
                <div class="modality-card__top">
                    <div class="modality-card__icon"><?php echo icon('dumbbell'); ?></div>
                    <span class="badge badge--<?php echo $active ? 'ativo' : 'inativo'; ?>"><?php echo $active ? 'Ativa' : 'Inativa'; ?></span>
                </div>
                <h2><?php echo e($item['nome']); ?></h2>
                <p class="modality-card__description"><?php echo e($item['descricao'] ?: 'Sem descrição cadastrada.'); ?></p>
                <div class="modality-card__schedule">
                    <span><?php echo icon('calendar'); ?> Grade publicada</span>
                    <strong><?php echo e($formatSchedule($schedules)); ?></strong>
                </div>
                <div class="modality-card__actions">
                    <a class="btn btn-secondary" href="<?php echo e(url('/modalidades/editar/' . (int) $item['id'])); ?>">
                        <?php echo icon('edit'); ?>
                        <span>Editar</span>
                    </a>
                    <?php if ($active): ?>
                        <form method="post" action="<?php echo e(url('/modalidades/inativar/' . (int) $item['id'])); ?>" class="inline-form" onsubmit="return confirm('Inativar esta modalidade? Ela deixará de aparecer na landing page.')">
                            <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                            <button type="submit" class="table-action table-action--danger" title="Inativar modalidade" aria-label="Inativar modalidade">
                                <?php echo icon('archive'); ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?php echo e(url('/modalidades/ativar/' . (int) $item['id'])); ?>" class="inline-form">
                            <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                            <button type="submit" class="table-action table-action--success" title="Ativar modalidade" aria-label="Ativar modalidade">
                                <?php echo icon('check-circle'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
