<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Services\BillingReminderMessage;
use PHPUnit\Framework\TestCase;

final class BillingReminderMessageTest extends TestCase
{
    public function testReminderUsesTheInvoiceAmountDueDateAndCompetence(): void
    {
        $message = BillingReminderMessage::compose(['aluno_nome' => 'Ana', 'modalidade_nome' => 'Karatê', 'competencia' => '2026-11', 'data_vencimento' => '2026-11-30', 'valor_final' => '149.90'], 'Garagem Aço');
        self::assertStringContainsString('Ana', $message);
        self::assertStringContainsString('30/11/2026', $message);
        self::assertStringContainsString('R$ 149,90', $message);
        self::assertStringContainsString('2026-11', $message);
    }
}
