<?php

declare(strict_types=1);

use Academia\Core\Csrf;

final class CsrfMiddleware
{
    public static function token(string $name = '_csrf'): string
    {
        return Csrf::token();
    }

    public static function check(string $name = '_csrf'): bool
    {
        $token = $_POST[$name] ?? $_POST['_csrf'] ?? $_POST['csrf'] ?? '';

        return is_string($token) && hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token);
    }

    public static function requireValid(): void
    {
        Csrf::verifyRequest();
    }
}
