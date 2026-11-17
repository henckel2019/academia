<?php

declare(strict_types=1);

namespace Academia\Repositories;

interface StudentManagementRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function list(): array;

    /** @return array{student: array<string, mixed>, enrollments: list<array<string, mixed>>}|null */
    public function find(int $id): ?array;

    /** @return list<array<string, mixed>> */
    public function activeModalities(): array;

    public function nextRegistrationCode(): string;

    /** @param array<string, mixed> $student @param list<array<string, mixed>> $enrollments @param array{email: string, password: string}|null $portalAccess */
    public function createStudent(array $student, array $enrollments, ?array $portalAccess = null): int;

    /** @param array<string, mixed> $student @param list<array<string, mixed>> $enrollments @param array{email: string, password: string}|null $portalAccess */
    public function updateStudent(int $id, array $student, array $enrollments, ?array $portalAccess = null): void;

    public function setStatus(int $id, string $status, bool $active): void;

    public function softDelete(int $id): void;
}
