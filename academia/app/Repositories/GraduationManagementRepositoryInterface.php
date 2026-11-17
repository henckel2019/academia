<?php

declare(strict_types=1);

namespace Academia\Repositories;

interface GraduationManagementRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function list(): array;

    /** @return list<array<string, mixed>> */
    public function activeStudents(): array;

    /** @return list<array<string, mixed>> */
    public function activeModalities(): array;

    /** @return list<array<string, mixed>> */
    public function activeProfessors(): array;

    /** @param array<string, mixed> $graduation @param array<string, mixed> $charge */
    public function createGraduation(array $graduation, array $charge): int;
}
