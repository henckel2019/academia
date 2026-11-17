<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Services\SettingsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SettingsServiceTest extends TestCase
{
    public function testAcceptsDefaultCheckinToleranceConfiguration(): void
    {
        $settings = (new SettingsService())->validate([
            'nome_academia' => 'Academia Garagem Aço', 'email' => '', 'dias_tolerancia_financeira' => '5',
            'dia_padrao_vencimento' => '10', 'moeda' => 'BRL', 'timezone' => 'America/Sao_Paulo',
            'tempo_expiracao_sessao' => '7200', 'max_tentativas_login' => '5', 'permitir_bloqueio_inadimplencia' => '1',
        ]);

        self::assertSame(5, $settings['dias_tolerancia_financeira']);
        self::assertTrue($settings['permitir_bloqueio_inadimplencia']);
    }

    public function testRejectsNegativeFinancialTolerance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new SettingsService())->validate([
            'nome_academia' => 'Academia', 'dias_tolerancia_financeira' => '-1', 'dia_padrao_vencimento' => '10',
            'moeda' => 'BRL', 'timezone' => 'America/Sao_Paulo', 'tempo_expiracao_sessao' => '7200', 'max_tentativas_login' => '5',
        ]);
    }
}
