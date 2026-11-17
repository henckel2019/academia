<?php

declare(strict_types=1);

use Academia\Core\ErrorHandler;
use Academia\Core\HttpException;
use Academia\Core\Url;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_money(mixed $value): string
{
    $amount = trim((string) $value);
    if (!preg_match('/^-?\d+(?:\.\d+)?$/', $amount)) {
        $amount = '0';
    }

    $negative = str_starts_with($amount, '-');
    $amount = ltrim($amount, '-');
    [$whole, $decimal] = array_pad(explode('.', $amount, 2), 2, '0');
    $whole = ltrim($whole, '0') ?: '0';
    $decimal = str_pad(substr($decimal, 0, 2), 2, '0');
    $groups = [];

    while (strlen($whole) > 3) {
        array_unshift($groups, substr($whole, -3));
        $whole = substr($whole, 0, -3);
    }
    array_unshift($groups, $whole);

    return ($negative ? '-' : '') . implode('.', $groups) . ',' . $decimal;
}

function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'dumbbell' => '<path d="M6.5 6.5v11M3.5 8.5v7M17.5 6.5v11M20.5 8.5v7M3.5 12h17"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'award' => '<circle cx="12" cy="8" r="5"/><path d="m8.5 12.5-1 8 4.5-2.5 4.5 2.5-1-8"/>',
        'user-round' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 12h6M9 16h4"/>',
        'qr' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3zM14 20h2"/>',
        'fingerprint' => '<path d="M12 11a2 2 0 0 1 2 2v6M8 19v-6a4 4 0 0 1 8 0v1M5 18v-5a7 7 0 0 1 14 0v2M3 16v-3a9 9 0 0 1 18 0v1M12 5a8 8 0 0 1 8 8"/>',
        'wallet' => '<path d="M3 6.5A2.5 2.5 0 0 1 5.5 4H19a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5.5A2.5 2.5 0 0 1 3 17.5z"/><path d="M3 7h16a2 2 0 0 1 2 2v3H17a2 2 0 0 0 0 4h4M17 14h.01"/>',
        'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3.4 13.4a2 2 0 0 1 0-2.8V4h6.6a2 2 0 0 1 1.4.6l9.2 9.2a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="m19.4 15 .1.1a2 2 0 1 1-2.8 2.8l-.1-.1a2 2 0 0 0-3.4 1.4v.3a2 2 0 1 1-4 0v-.3a2 2 0 0 0-3.4-1.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A2 2 0 0 0 3.6 11H3.3a2 2 0 1 1 0-4h.3a2 2 0 0 0 1.4-3.4l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A2 2 0 0 0 11.2 2h.3a2 2 0 1 1 4 0v.3a2 2 0 0 0 3.4 1.4l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A2 2 0 0 0 20.4 7h.3a2 2 0 1 1 0 4h-.3a2 2 0 0 0-1 4Z"/>',
        'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'archive' => '<path d="M3 6h18M5 6v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6M9 10h6M4 3h16v3H4z"/>',
        'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
        'arrow-left' => '<path d="m12 19-7-7 7-7M5 12h14"/>',
        'arrow-right' => '<path d="m12 5 7 7-7 7M5 12h14"/>',
        'arrow-down' => '<path d="m5 12 7 7 7-7M12 5v14"/>',
        'external' => '<path d="M14 3h7v7M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
        'alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'more' => '<circle cx="5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1" fill="currentColor" stroke="none"/>',
        'home' => '<path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21v-6h6v6"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

function app_config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['app_config'] ?? [];
    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

function url(string $path = '/'): string
{
    /** @var Url $url */
    $url = $GLOBALS['url_generator'];

    return $url->to($path);
}

function asset(string $path): string
{
    /** @var Url $url */
    $url = $GLOBALS['url_generator'];
    $assetUrl = $url->asset($path);
    $assetPath = parse_url($path, PHP_URL_PATH);
    $filePath = dirname(__DIR__) . '/public/' . ltrim((string) $assetPath, '/');

    if (is_file($filePath) && !str_contains($assetUrl, '?')) {
        return $assetUrl . '?v=' . (string) filemtime($filePath);
    }

    return $assetUrl;
}

function landing_schedule_cache_file(): string
{
    return dirname(__DIR__) . '/storage/cache/landing-modalities.json';
}

/** @return list<array<string, mixed>> */
function read_landing_schedule_cache(): array
{
    $path = landing_schedule_cache_file();
    if (!is_file($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $decoded = $content === false ? null : json_decode($content, true);

    return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function abort(int $status, string $message = ''): never
{
    ErrorHandler::render(new HttpException($status, $message));
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;

        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return is_string($value) ? $value : null;
}
