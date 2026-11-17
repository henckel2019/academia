<?php

declare(strict_types=1);

namespace Academia\Repositories;

interface DashboardRepositoryInterface
{
    /** @return array<string, mixed> */
    public function stats(string $competencia): array;

    /** @return array<string, mixed> */
    public function financialSummary(string $competencia): array;

    /** @return list<array<string, mixed>> */
    public function invoicesByStatus(string $competencia, string $status): array;
}
