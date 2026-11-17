<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
echo (new \Academia\Repositories\BillingRepository($pdo))->markOverdue() . " cobrança(s) vencida(s).\n";
