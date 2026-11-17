<?php

declare(strict_types=1);

namespace Academia\Repositories;

interface ModalityManagementRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function list(): array;

    /** @return array{modality: array<string, mixed>, schedules: list<array<string, mixed>>}|null */
    public function find(int $id): ?array;

    /** @param array<string, mixed> $modality @param list<array<string, mixed>> $schedules */
    public function createModality(array $modality, array $schedules): int;

    /** @param array<string, mixed> $modality @param list<array<string, mixed>> $schedules */
    public function updateModality(int $id, array $modality, array $schedules): void;

    public function setActive(int $id, bool $active): void;

    /** @return list<array<string, mixed>> */
    public function publicSchedule(): array;
}
