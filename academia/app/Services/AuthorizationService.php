<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Core\HttpException;
use Academia\Support\Role;

final class AuthorizationService
{
    /** @param list<string> $allowedRoles */
    public static function requireAnyRole(array $allowedRoles): void
    {
        $role = (string) ($_SESSION['auth']['role'] ?? '');
        if (!in_array($role, $allowedRoles, true)) {
            throw new HttpException(403);
        }
    }

    public static function requireAuthenticated(): void
    {
        if (empty($_SESSION['auth']['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public static function isAdministrator(): bool
    {
        return in_array($_SESSION['auth']['role'] ?? '', [Role::SUPER_ADMIN, Role::ADMIN], true);
    }
}
