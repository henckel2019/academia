<?php
// app/middleware/CsrfMiddleware.php
class CsrfMiddleware {
    public static function token(string $name = 'csrf'): string {
        if (empty($_SESSION[$name])) {
            $_SESSION[$name] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$name];
    }

    public static function check(string $name = 'csrf'): bool {
        $token = $_POST[$name] ?? '';
        return hash_equals($_SESSION[$name] ?? '', $token);
    }
}
