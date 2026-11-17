<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Repositories\DashboardRepositoryInterface;
use Academia\Services\DashboardService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DashboardServiceTest extends TestCase
{
    public function testBuildsTheCurrentMonthDashboardWithTheNextMonthForecast(): void
    {
        $repository = $this->createMock(DashboardRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('stats')
            ->with('2026-08')
            ->willReturn([
                'alunos_ativos' => '12',
                'turmas_ativas' => '4',
                'mensalidades_pagas' => '9',
                'mensalidades_vencidas' => '2',
            ]);
        $repository->expects(self::once())
            ->method('financialSummary')
            ->with('2026-08')
            ->willReturn([
                'faturamento_mes' => '1800.00',
                'recebido_mes' => '1500.00',
                'previsao_proximo_mes' => '2200.00',
            ]);
        $repository->expects(self::exactly(2))
            ->method('invoicesByStatus')
            ->willReturnMap([
                ['2026-08', 'PAGA', [['aluno_nome' => 'Ana']]],
                ['2026-08', 'VENCIDA', [['aluno_nome' => 'Bruno']]],
            ]);

        $summary = (new DashboardService($repository))->summary(new DateTimeImmutable('2026-08-31'));

        self::assertSame('2026-08', $summary['competencia']);
        self::assertSame('Agosto de 2026', $summary['competencia_label']);
        self::assertSame('Setembro de 2026', $summary['proxima_competencia_label']);
        self::assertSame(12, $summary['stats']['alunos_ativos']);
        self::assertSame(4, $summary['stats']['turmas_ativas']);
        self::assertSame(9, $summary['stats']['mensalidades_pagas']);
        self::assertSame(2, $summary['stats']['mensalidades_vencidas']);
        self::assertSame('1800.00', $summary['financeiro']['faturamento_mes']);
        self::assertSame('2200.00', $summary['financeiro']['previsao_proximo_mes']);
        self::assertSame('Ana', $summary['mensalidades_pagas'][0]['aluno_nome']);
        self::assertSame('Bruno', $summary['mensalidades_vencidas'][0]['aluno_nome']);
    }
}
