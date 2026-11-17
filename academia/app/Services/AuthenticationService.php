<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\AuditRepository;
use Academia\Repositories\LoginAttemptRepository;
use Academia\Repositories\UserRepository;

final class AuthenticationService
{
    public function __construct(private readonly UserRepository $users, private readonly LoginAttemptRepository $attempts, private readonly AuditRepository $audit, private readonly int $maxAttempts, private readonly int $lockoutSeconds)
    {
    }

    /** @return array<string, mixed>|null */
    public function authenticate(string $email, string $password, string $ip): ?array
    {
        if ($this->attempts->isBlocked($email, $ip, $this->maxAttempts, $this->lockoutSeconds)) {
            return null;
        }
        $user = $this->users->findActiveByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['senha_hash'])) {
            $this->attempts->recordFailure($email, $ip);

            return null;
        }
        $this->attempts->clearFailures($email, $ip);
        $this->users->registerLogin((int) $user['id']);
        $this->audit->record((int) $user['id'], 'LOGIN_SUCESSO', 'usuarios', (int) $user['id']);

        return $user;
    }
}
