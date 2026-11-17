<?php

declare(strict_types=1);

namespace Academia\Core;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf'];
    }

    public static function verifyRequest(): void
    {
        $token = $_POST['_csrf'] ?? Request::header('X-CSRF-Token') ?? '';
        if (!is_string($token) || !hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token)) {
            throw new HttpException(419, 'Sua sessão de formulário expirou. Atualize a página e tente novamente.');
        }
    }
}
