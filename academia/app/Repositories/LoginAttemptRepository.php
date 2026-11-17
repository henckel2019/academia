<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class LoginAttemptRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function isBlocked(string $email, string $ip, int $maxAttempts, int $lockoutSeconds): bool
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE sucesso = FALSE AND (email_hash = :email_hash OR ip_hash = :ip_hash) AND attempted_at >= CURRENT_TIMESTAMP - (:lockout_seconds * INTERVAL '1 second')");
        $statement->execute(['email_hash' => $this->hash($email), 'ip_hash' => $this->hash($ip), 'lockout_seconds' => $lockoutSeconds]);

        return (int) $statement->fetchColumn() >= $maxAttempts;
    }

    public function recordFailure(string $email, string $ip): void
    {
        $this->pdo->prepare('INSERT INTO login_attempts (email_hash, ip_hash, sucesso) VALUES (:email_hash, :ip_hash, FALSE)')->execute(['email_hash' => $this->hash($email), 'ip_hash' => $this->hash($ip)]);
    }

    public function clearFailures(string $email, string $ip): void
    {
        $this->pdo->prepare('DELETE FROM login_attempts WHERE (email_hash = :email_hash OR ip_hash = :ip_hash) AND sucesso = FALSE')->execute(['email_hash' => $this->hash($email), 'ip_hash' => $this->hash($ip)]);
    }

    private function hash(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }
}
