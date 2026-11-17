<?php

declare(strict_types=1);

namespace Academia\Services;

use DateTimeImmutable;

final class BillingReminderMessage
{
    public static function compose(array $bill, string $academy): string
    {
        $date = (new DateTimeImmutable($bill['data_vencimento']))->format('d/m/Y');
        $amount = \format_money($bill['valor_final']);
        return "Olá, {$bill['aluno_nome']}! Aqui é da {$academy}.\n\n"
            . "Sua mensalidade de {$bill['modalidade_nome']}, competência {$bill['competencia']}, tem vencimento em {$date}.\n"
            . "Valor: R$ {$amount}.\n\n"
            . "Se já realizou o pagamento, envie o comprovante para a recepção conferir. Para dúvidas, responda esta mensagem. Obrigado!";
    }
}
