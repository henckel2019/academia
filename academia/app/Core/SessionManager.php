<?php

declare(strict_types=1);

namespace Academia\Core;

final class SessionManager
{
    public static function start(array $config): void
    {
        if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = max(300, (int) ($config['session_lifetime'] ?? 7200));
        $secure = (($config['environment'] ?? 'production') === 'production') || str_starts_with((string) ($config['url'] ?? ''), 'https://');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);
        session_name((string) ($config['session_name'] ?? 'academia_session'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        $lastActivity = (int) ($_SESSION['_last_activity'] ?? time());
        if ($lastActivity + $lifetime < time()) {
            self::destroy();
            session_start();
            $_SESSION['_expired'] = true;
        }

        $_SESSION['_last_activity'] = time();
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'] ?: '/',
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
        session_destroy();
    }
}
