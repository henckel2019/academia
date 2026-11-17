<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT u.id, u.nome, u.email, u.senha_hash, u.primeiro_acesso, p.nome AS role FROM usuarios u INNER JOIN perfis p ON p.id = u.perfil_id WHERE LOWER(u.email) = LOWER(:email) AND u.ativo = TRUE AND u.deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function registerLogin(int $userId): void
    {
        $this->pdo->prepare('UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id')->execute(['id' => $userId]);
    }
}
