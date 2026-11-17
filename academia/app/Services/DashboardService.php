<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\DashboardRepositoryInterface;
use DateTimeImmutable;

final class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $repository)
    {
    }

    /** @return array<string, mixed> */
    public function summary(?DateTimeImmutable $today = null): array
    {
        $currentMonth = ($today ?? new DateTimeImmutable('today'))->modify('first day of this month');
        $nextMonth = $currentMonth->modify('+1 month');
        $competencia = $currentMonth->format('Y-m');

        $stats = $this->repository->stats($competencia);
        $financialSummary = $this->repository->financialSummary($competencia);

        return [
            'competencia' => $competencia,
            'competencia_label' => $this->monthLabel($currentMonth),
            'proxima_competencia_label' => $this->monthLabel($nextMonth),
            'stats' => [
                'alunos_ativos' => (int) ($stats['alunos_ativos'] ?? 0),
                'turmas_ativas' => (int) ($stats['turmas_ativas'] ?? 0),
                'mensalidades_pagas' => (int) ($stats['mensalidades_pagas'] ?? 0),
                'mensalidades_vencidas' => (int) ($stats['mensalidades_vencidas'] ?? 0),
            ],
            'financeiro' => [
                'faturamento_mes' => (string) ($financialSummary['faturamento_mes'] ?? '0.00'),
                'recebido_mes' => (string) ($financialSummary['recebido_mes'] ?? '0.00'),
                'previsao_proximo_mes' => (string) ($financialSummary['previsao_proximo_mes'] ?? '0.00'),
            ],
            'mensalidades_pagas' => $this->repository->invoicesByStatus($competencia, 'PAGA'),
            'mensalidades_vencidas' => $this->repository->invoicesByStatus($competencia, 'VENCIDA'),
        ];
    }

    private function monthLabel(DateTimeImmutable $month): string
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $months[(int) $month->format('n')] . ' de ' . $month->format('Y');
    }
}
