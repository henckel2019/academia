<?php
// app/middleware/AuthMiddleware.php
class AuthMiddleware {
    public static function requireAuth(): bool {
        if (empty($_SESSION['user_id'])) {
            header('Location: /academia/public/login.php');
            exit;
        }
        return true;
    }

    public static function requireRole(string ...$allowed): bool {
        self::requireAuth();
        if (!in_array($_SESSION['perfil'] ?? '', $allowed, true)) {
            http_response_code(403);
            echo 'Acesso negado.';
            exit;
        }
        return true;
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }
}
