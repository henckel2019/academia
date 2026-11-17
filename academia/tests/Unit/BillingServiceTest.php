<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Services\BillingService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BillingServiceTest extends TestCase
{
    public function testAcceptsValidCompetence(): void
    {
        BillingService::validateMonth('2026-02');
        $this->addToAssertionCount(1);
    }

    public function testRejectsInvalidMonthBeforeDatabaseMutation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BillingService::validateMonth('2026-13');
    }
}
