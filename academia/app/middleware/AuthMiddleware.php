<?php

declare(strict_types=1);

use Academia\Services\AuthorizationService;

final class AuthMiddleware
{
    public static function requireAuth(): bool
    {
        AuthorizationService::requireAuthenticated();

        return true;
    }

    public static function requireRole(string ...$allowed): bool
    {
        self::requireAuth();
        AuthorizationService::requireAnyRole($allowed);

        return true;
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }
}
