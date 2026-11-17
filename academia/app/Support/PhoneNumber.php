<?php

declare(strict_types=1);

namespace Academia\Support;

use InvalidArgumentException;

final class PhoneNumber
{
    public static function brazil(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input);
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }
        if (!preg_match('/^55[1-9][0-9][0-9]{8,9}$/D', $digits)) {
            throw new InvalidArgumentException('Informe um telefone com DDD válido para a cobrança por WhatsApp.');
        }
        return $digits;
    }
}
