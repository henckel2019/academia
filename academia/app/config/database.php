<?php

declare(strict_types=1);

use Academia\Core\Env;

return [
    'driver' => Env::get('DB_DRIVER', 'pgsql'),
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::int('DB_PORT', 5432),
    'database' => Env::get('DB_DATABASE', 'academia_garagem_aco'),
    'username' => Env::get('DB_USERNAME', 'academia'),
    'password' => Env::get('DB_PASSWORD', ''),
    'sslmode' => Env::get('DB_SSLMODE', 'prefer'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => true,
        PDO::ATTR_PERSISTENT => Env::bool('DB_PERSISTENT', true),
    ],
];
