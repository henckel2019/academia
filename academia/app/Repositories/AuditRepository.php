<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class AuditRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed>|null $after */
    public function record(?int $userId, string $action, string $entity, ?int $entityId, ?array $before = null, ?array $after = null): void
    {
        $statement = $this->pdo->prepare('INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, dados_antes, dados_depois, ip, user_agent) VALUES (:usuario_id, :acao, :entidade, :entidade_id, CAST(:dados_antes AS JSONB), CAST(:dados_depois AS JSONB), CAST(:ip AS INET), :user_agent)');
        $statement->execute([
            'usuario_id' => $userId, 'acao' => $action, 'entidade' => $entity, 'entidade_id' => $entityId,
            'dados_antes' => $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR),
            'dados_depois' => $after === null ? null : json_encode($after, JSON_THROW_ON_ERROR),
            'ip' => \Academia\Core\Request::clientIp(), 'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    }
}
