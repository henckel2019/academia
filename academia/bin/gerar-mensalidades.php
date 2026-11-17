<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$month = $argv[1] ?? date('Y-m');
\Academia\Services\BillingService::validateMonth($month);
// Automation has no logged-in actor; SQL audit accepts a nullable user.
$repository = new \Academia\Repositories\BillingRepository($pdo);
echo $repository->generate($month, null) . " mensalidade(s) gerada(s).\n";
