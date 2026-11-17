<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Services\CheckinAccessService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CheckinAccessServiceTest extends TestCase
{
    private CheckinAccessService $service;

    protected function setUp(): void
    {
        $this->service = new CheckinAccessService();
    }

    public function testAllowsStudentWithinFiveDayToleranceWithClearReason(): void
    {
        $decision = $this->service->decide('ATIVO', 'ATIVA', 'VENCIDA', '2026-08-29', 5, true, new DateTimeImmutable('2026-08-31'));
        self::assertTrue($decision->allowed);
        self::assertSame('LIBERADO COM ALERTA', $decision->title);
        self::assertStringContainsString('3 dias restantes', $decision->message);
    }

    public function testBlocksStudentAfterTolerance(): void
    {
        $decision = $this->service->decide('ATIVO', 'ATIVA', 'VENCIDA', '2026-08-23', 5, true, new DateTimeImmutable('2026-08-31'));
        self::assertFalse($decision->allowed);
        self::assertSame('BLOQUEADO', $decision->title);
        self::assertStringContainsString('8 dias', $decision->message);
    }

    public function testBlocksSuspendedEnrollmentBeforeCheckingFinance(): void
    {
        $decision = $this->service->decide('ATIVO', 'SUSPENSA', 'PAGA', '2026-08-10', 5, true, new DateTimeImmutable('2026-08-31'));
        self::assertFalse($decision->allowed);
        self::assertStringContainsString('Matrícula suspensa', $decision->message);
    }

    public function testKeepsOverdueWarningWhenBlockingIsDisabled(): void
    {
        $decision = $this->service->decide('ATIVO', 'ATIVA', 'ABERTA', '2026-08-01', 5, false, new DateTimeImmutable('2026-08-31'));
        self::assertTrue($decision->allowed);
        self::assertSame('LIBERADO COM ALERTA', $decision->title);
        self::assertStringContainsString('30 dias', $decision->message);
    }

    public function testDueTodayIsNotOverdue(): void
    {
        $decision = $this->service->decide('ATIVO', 'ATIVA', 'ABERTA', '2026-08-31', 0, true, new DateTimeImmutable('2026-08-31'));
        self::assertTrue($decision->allowed);
        self::assertSame('LIBERADO', $decision->title);
    }
}
