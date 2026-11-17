<?php

declare(strict_types=1);

// Router used only by PHP's built-in development server. Apache/Nginx use
// their own rewrite configuration and continue to target index.php directly.
$uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$file = __DIR__ . DIRECTORY_SEPARATOR . ltrim($uri, '/');

if (PHP_SAPI === 'cli-server' && is_file($file)) {
    $extension = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'mjs' => 'text/javascript; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    if (isset($mimeTypes[$extension])) {
        header('Cache-Control: public, max-age=604800, immutable');
        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Content-Length: ' . (string) filesize($file));
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            readfile($file);
        }
        exit;
    }
    return false;
}

require __DIR__ . '/index.php';
