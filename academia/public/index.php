<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$requestPath = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
$basePath = $GLOBALS['url_generator']->basePath();
if ($basePath !== '' && ($requestPath === $basePath || str_starts_with($requestPath, $basePath . '/'))) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}

$path = trim(urldecode($requestPath), '/');
if (in_array($path, ['index.php', 'login.php'], true)) {
    $path = $path === 'login.php' ? 'login' : '';
}

require dirname(__DIR__) . '/app/routes/web.php';
