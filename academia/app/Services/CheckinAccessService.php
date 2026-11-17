<?php

declare(strict_types=1);

namespace Academia\Services;

use DateTimeImmutable;

final class CheckinAccessService
{
    public function decide(string $studentStatus, string $enrollmentStatus, ?string $billStatus, ?string $dueDate, int $toleranceDays, bool $blockDelinquency, DateTimeImmutable $today): AccessDecision
    {
        if ($studentStatus !== 'ATIVO') {
            return new AccessDecision('BLOCKED', 'BLOQUEADO', 'Aluno inativo, suspenso ou cancelado.', false);
        }
        if ($enrollmentStatus !== 'ATIVA') {
            return new AccessDecision('BLOCKED', 'BLOQUEADO', 'Matrícula suspensa ou cancelada.', false);
        }
        if ($billStatus === null || $billStatus === 'PAGA' || $billStatus === 'CANCELADA' || $dueDate === null) {
            return new AccessDecision('ALLOWED', 'LIBERADO', 'Mensalidade em dia.', true);
        }
        $due = new DateTimeImmutable($dueDate);
        if ($due >= $today) {
            return new AccessDecision('ALLOWED', 'LIBERADO', 'Mensalidade em dia.', true);
        }
        $daysLate = (int) $due->diff($today)->format('%a');
        if (!$blockDelinquency) {
            return new AccessDecision('ALLOWED_WARNING', 'LIBERADO COM ALERTA', "Mensalidade vencida há {$daysLate} dias. Bloqueio financeiro desativado.", true);
        }
        if ($daysLate <= $toleranceDays) {
            $remaining = $toleranceDays - $daysLate;
            return new AccessDecision('ALLOWED_WARNING', 'LIBERADO COM ALERTA', "Mensalidade vencida há {$daysLate} dias. {$remaining} dias restantes de tolerância.", true);
        }

        return new AccessDecision('BLOCKED', 'BLOQUEADO', "Mensalidade vencida há {$daysLate} dias.", false);
    }
}
