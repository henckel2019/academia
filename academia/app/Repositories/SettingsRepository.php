<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class SettingsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT chave, valor FROM configuracoes ORDER BY chave')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['chave']] = json_decode((string) $row['valor'], true, 512, JSON_THROW_ON_ERROR);
        }

        return $settings;
    }

    /** @param array<string, mixed> $values */
    public function save(array $values, int $userId): void
    {
        $statement = $this->pdo->prepare('INSERT INTO configuracoes (chave, valor, atualizado_por_usuario_id, updated_at) VALUES (:chave, CAST(:valor AS JSONB), :usuario_id, CURRENT_TIMESTAMP) ON CONFLICT (chave) DO UPDATE SET valor = EXCLUDED.valor, atualizado_por_usuario_id = EXCLUDED.atualizado_por_usuario_id, updated_at = CURRENT_TIMESTAMP');
        foreach ($values as $key => $value) {
            $statement->execute(['chave' => $key, 'valor' => json_encode($value, JSON_THROW_ON_ERROR), 'usuario_id' => $userId]);
        }
    }
}
