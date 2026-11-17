<?php

declare(strict_types=1);

use Academia\Repositories\ModalityManagementRepository;
use Academia\Services\ModalityManagementService;

require dirname(__DIR__) . '/app/bootstrap.php';
$repository = new ModalityManagementRepository($pdo);
$service = new ModalityManagementService($repository);
$existing = [];
foreach ($repository->list() as $modality) {
    $existing[mb_strtolower($modality['nome'])] = $modality;
}
$count = 0;
foreach (require dirname(__DIR__) . '/app/config/initial-schedule.php' as $modality) {
    $current = $existing[mb_strtolower($modality['nome'])] ?? null;
    // A repeated import must preserve a timetable already maintained by the team.
    if ($current !== null && $current['horarios'] !== []) {
        continue;
    }
    $schedules = array_map(static fn (array $lesson): array => [
        'dia_semana' => $lesson[0], 'horario_inicio' => $lesson[1], 'horario_fim' => '', 'observacao' => $lesson[2] ?? '',
    ], $modality['aulas']);
    $input = ['nome' => $modality['nome'], 'descricao' => $modality['descricao'], 'horarios' => $schedules];
    if ($current === null) {
        $service->create($input);
    } else {
        $service->update((int) $current['id'], $input);
    }
    ++$count;
}
echo "{$count} modalidade(s) receberam a grade inicial. Horários existentes foram preservados.\n";
