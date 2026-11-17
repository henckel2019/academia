<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\BillingRepository;
use InvalidArgumentException;

final class BillingService
{
    public function __construct(private readonly BillingRepository $repository)
    {
    }

    public static function validateMonth(string $month): void
    {
        if (!preg_match('/^(20[0-9]{2})-(0[1-9]|1[0-2])$/D', $month)) {
            throw new InvalidArgumentException('Informe uma competência válida entre 2000 e 2099.');
        }
    }

    public function generate(string $month, int $userId): int
    {
        self::validateMonth($month);
        return $this->repository->generate($month, $userId);
    }

    public function settle(int $billId, string $method, int $userId): bool
    {
        if ($billId < 1 || !in_array($method, ['PIX', 'BOLETO', 'CARTAO', 'DINHEIRO', 'OUTRO'], true)) {
            throw new InvalidArgumentException('Selecione uma cobrança e uma forma de pagamento válida.');
        }
        return $this->repository->settle($billId, $method, $userId);
    }
}
