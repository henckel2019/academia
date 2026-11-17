<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\ModalityManagementRepositoryInterface;
use InvalidArgumentException;

final class ModalityManagementService
{
    public function __construct(private readonly ModalityManagementRepositoryInterface $repository)
    {
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): int
    {
        $name = $this->validateName($input);
        $modality = [
            'nome' => $name,
            'descricao' => trim((string) ($input['descricao'] ?? '')),
            'ativo' => true,
        ];

        return $this->repository->createModality($modality, $this->normalizeSchedules($input['horarios'] ?? []));
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): void
    {
        $name = $this->validateName($input);
        $modality = [
            'nome' => $name,
            'descricao' => trim((string) ($input['descricao'] ?? '')),
        ];

        $this->repository->updateModality($id, $modality, $this->normalizeSchedules($input['horarios'] ?? []));
    }

    public function activate(int $id): void
    {
        $this->repository->setActive($id, true);
    }

    public function inactivate(int $id): void
    {
        $this->repository->setActive($id, false);
    }

    /** @param array<string, mixed> $input */
    private function validateName(array $input): string
    {
        $name = trim((string) ($input['nome'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Informe o nome da modalidade.');
        }
        if (mb_strlen($name) > 100) {
            throw new InvalidArgumentException('O nome da modalidade deve ter no máximo 100 caracteres.');
        }

        return $name;
    }

    /** @return list<array<string, mixed>> */
    private function normalizeSchedules(mixed $rawSchedules): array
    {
        $schedules = [];
        $seen = [];
        $rows = is_array($rawSchedules) ? $rawSchedules : [];

        foreach ($rows as $schedule) {
            if (!is_array($schedule)) {
                continue;
            }

            $day = filter_var($schedule['dia_semana'] ?? null, FILTER_VALIDATE_INT);
            $start = trim((string) ($schedule['horario_inicio'] ?? ''));
            $end = trim((string) ($schedule['horario_fim'] ?? ''));
            if ($day === false || $day < 1 || $day > 7) {
                throw new InvalidArgumentException('Escolha um dia da semana válido.');
            }
            if (!$this->isTime($start) || ($end !== '' && !$this->isTime($end))) {
                throw new InvalidArgumentException('Informe horários válidos para a modalidade.');
            }
            if ($end !== '' && $end <= $start) {
                throw new InvalidArgumentException('O horário final deve ser depois do horário inicial.');
            }

            $key = $day . '|' . $start;
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Não é possível repetir o mesmo horário da modalidade.');
            }
            $seen[$key] = true;
            $schedules[] = [
                'dia_semana' => (int) $day,
                'horario_inicio' => $start,
                'horario_fim' => $end === '' ? null : $end,
                'ativo' => true,
            ];
            $note = trim((string) ($schedule['observacao'] ?? ''));
            if (mb_strlen($note) > 150) {
                throw new InvalidArgumentException('A observação do horário deve ter até 150 caracteres.');
            }
            if ($note !== '') {
                $schedules[array_key_last($schedules)]['observacao'] = $note;
            }
        }

        usort($schedules, static function (array $left, array $right): int {
            return [$left['dia_semana'], $left['horario_inicio']] <=> [$right['dia_semana'], $right['horario_inicio']];
        });

        return $schedules;
    }

    private function isTime(string $time): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }
}
