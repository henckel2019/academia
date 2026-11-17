<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;
use Throwable;

final class ModalityManagementRepository implements ModalityManagementRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        $modalities = $this->pdo->query(
            'SELECT id, nome, descricao, ativo, deleted_at
             FROM modalidades
             WHERE deleted_at IS NULL
             ORDER BY nome ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($modalities) || $modalities === []) {
            return [];
        }

        $schedules = $this->pdo->query(
            "SELECT id, modalidade_id, dia_semana, TO_CHAR(horario_inicio, 'HH24:MI') AS horario_inicio,
                    TO_CHAR(horario_fim, 'HH24:MI') AS horario_fim, ativo, observacao
             FROM modalidade_horarios
             WHERE ativo = TRUE
             ORDER BY modalidade_id, dia_semana, horario_inicio"
        )->fetchAll(PDO::FETCH_ASSOC);

        return $this->attachSchedules($modalities, $schedules);
    }

    /** @return array{modality: array<string, mixed>, schedules: list<array<string, mixed>>}|null */
    public function find(int $id): ?array
    {
        $modalityStatement = $this->pdo->prepare(
            'SELECT id, nome, descricao, ativo, deleted_at
             FROM modalidades
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $modalityStatement->execute(['id' => $id]);
        $modality = $modalityStatement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($modality)) {
            return null;
        }

        $scheduleStatement = $this->pdo->prepare(
            "SELECT id, modalidade_id, dia_semana, TO_CHAR(horario_inicio, 'HH24:MI') AS horario_inicio,
                    TO_CHAR(horario_fim, 'HH24:MI') AS horario_fim, ativo, observacao
             FROM modalidade_horarios
             WHERE modalidade_id = :modality_id AND ativo = TRUE
             ORDER BY dia_semana, horario_inicio"
        );
        $scheduleStatement->execute(['modality_id' => $id]);
        $schedules = $scheduleStatement->fetchAll(PDO::FETCH_ASSOC);

        return [
            'modality' => $modality,
            'schedules' => is_array($schedules) ? $schedules : [],
        ];
    }

    /** @param array<string, mixed> $modality @param list<array<string, mixed>> $schedules */
    public function createModality(array $modality, array $schedules): int
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO modalidades (nome, descricao, ativo)
                 VALUES (:nome, NULLIF(:descricao, \'\'), :ativo)
                 RETURNING id'
            );
            $statement->execute($modality);
            $id = (int) $statement->fetchColumn();
            $this->insertSchedules($id, $schedules);
            $this->pdo->commit();
            $this->invalidatePublicScheduleCache();

            return $id;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $modality @param list<array<string, mixed>> $schedules */
    public function updateModality(int $id, array $modality, array $schedules): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'UPDATE modalidades
                 SET nome = :nome, descricao = NULLIF(:descricao, \'\'), updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $statement->execute($modality + ['id' => $id]);

            $delete = $this->pdo->prepare('DELETE FROM modalidade_horarios WHERE modalidade_id = :modality_id');
            $delete->execute(['modality_id' => $id]);
            $this->insertSchedules($id, $schedules);
            $this->pdo->commit();
            $this->invalidatePublicScheduleCache();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function setActive(int $id, bool $active): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE modalidades
             SET ativo = :ativo, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute(['ativo' => $active, 'id' => $id]);
        $this->invalidatePublicScheduleCache();
    }

    /** @return list<array<string, mixed>> */
    public function publicSchedule(): array
    {
        $modalities = $this->pdo->query(
            'SELECT id, nome, descricao, ativo
             FROM modalidades
             WHERE ativo = TRUE AND deleted_at IS NULL
             ORDER BY nome ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($modalities) || $modalities === []) {
            return [];
        }

        $schedules = $this->pdo->query(
            "SELECT mh.modalidade_id, mh.dia_semana,
                    TO_CHAR(mh.horario_inicio, 'HH24:MI') AS horario_inicio,
                    TO_CHAR(mh.horario_fim, 'HH24:MI') AS horario_fim, mh.observacao
             FROM modalidade_horarios mh
             INNER JOIN modalidades m ON m.id = mh.modalidade_id
             WHERE mh.ativo = TRUE AND m.ativo = TRUE AND m.deleted_at IS NULL
             ORDER BY mh.modalidade_id, mh.dia_semana, mh.horario_inicio"
        )->fetchAll(PDO::FETCH_ASSOC);

        return $this->attachSchedules($modalities, $schedules);
    }

    /** @param list<array<string, mixed>> $schedules */
    private function insertSchedules(int $modalityId, array $schedules): void
    {
        if ($schedules === []) {
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO modalidade_horarios (modalidade_id, dia_semana, horario_inicio, horario_fim, ativo, observacao)
             VALUES (:modalidade_id, :dia_semana, :horario_inicio, :horario_fim, :ativo, :observacao)'
        );
        foreach ($schedules as $schedule) {
            $statement->execute($schedule + ['modalidade_id' => $modalityId, 'observacao' => null]);
        }
    }

    /** @param list<array<string, mixed>> $modalities @param list<array<string, mixed>> $schedules @return list<array<string, mixed>> */
    private function attachSchedules(array $modalities, array $schedules): array
    {
        $byModality = [];
        foreach ($schedules as $schedule) {
            $byModality[(int) ($schedule['modalidade_id'] ?? 0)][] = $schedule;
        }

        foreach ($modalities as &$modality) {
            $modality['horarios'] = $byModality[(int) $modality['id']] ?? [];
        }
        unset($modality);

        return $modalities;
    }

    private function invalidatePublicScheduleCache(): void
    {
        $path = dirname(__DIR__, 2) . '/storage/cache/landing-modalities.json';
        if (is_file($path)) {
            unlink($path);
        }
    }
}
