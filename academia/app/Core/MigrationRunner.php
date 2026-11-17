<?php

declare(strict_types=1);

namespace Academia\Core;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    public function __construct(private readonly PDO $pdo, private readonly string $directory)
    {
    }

    public function migrate(): int
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(180) PRIMARY KEY, applied_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP)');
        $files = glob($this->directory . '/*.php') ?: [];
        sort($files, SORT_STRING);
        $applied = 0;

        foreach ($files as $file) {
            $version = basename($file, '.php');
            $check = $this->pdo->prepare('SELECT 1 FROM schema_migrations WHERE version = :version');
            $check->execute(['version' => $version]);
            if ($check->fetchColumn()) {
                continue;
            }

            $migration = require $file;
            if (!is_callable($migration)) {
                throw new RuntimeException("Migration inválida: {$version}");
            }

            $this->pdo->beginTransaction();
            try {
                $migration($this->pdo);
                $this->pdo->prepare('INSERT INTO schema_migrations (version) VALUES (:version)')->execute(['version' => $version]);
                $this->pdo->commit();
                $applied++;
            } catch (\Throwable $exception) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $exception;
            }
        }

        return $applied;
    }
}
