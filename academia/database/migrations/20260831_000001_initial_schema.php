<?php

declare(strict_types=1);

return static function (\PDO $pdo): void {
    $schema = file_get_contents(__DIR__ . '/../schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler o schema PostgreSQL.');
    }

    $pdo->exec($schema);
};
