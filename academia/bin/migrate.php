<?php

declare(strict_types=1);

use Academia\Core\MigrationRunner;

require dirname(__DIR__) . '/app/bootstrap.php';

$runner = new MigrationRunner($pdo, dirname(__DIR__) . '/database/migrations');
$count = $runner->migrate();
echo $count === 0 ? "Nenhuma migration pendente.\n" : "{$count} migration(s) aplicada(s).\n";
