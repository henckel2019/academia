<?php

declare(strict_types=1);

use Academia\Core\Env;
use Academia\Core\ErrorHandler;
use Academia\Core\SessionManager;
use Academia\Core\Url;

$projectRoot = dirname(__DIR__);

$composerAutoload = $projectRoot . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

spl_autoload_register(static function (string $class) use ($projectRoot): void {
    if (str_starts_with($class, 'Academia\\')) {
        $file = $projectRoot . '/app/' . str_replace('\\', '/', substr($class, strlen('Academia\\'))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }

        return;
    }

    $legacy = ['BaseModel', 'Aluno', 'Modalidade', 'Turma', 'Graduacao', 'Frequencia', 'Usuario', 'Mensalidade', 'Plano', 'Matricula', 'Promocao', 'Professor'];
    if (in_array($class, $legacy, true)) {
        $file = $projectRoot . '/app/models/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

Env::load($projectRoot . '/.env');
$appConfig = require $projectRoot . '/app/config/app.php';
$dbConfig = require $projectRoot . '/app/config/database.php';

require_once $projectRoot . '/app/helpers.php';
$GLOBALS['app_config'] = $appConfig;

$configuredUrl = (string) $appConfig['url'];
$configuredUrlParts = parse_url($configuredUrl) ?: [];
$configuredHost = strtolower((string) ($configuredUrlParts['host'] ?? ''));
$requestHost = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''))[0]);
$requestHostName = strtolower((string) (parse_url('http://' . $requestHost, PHP_URL_HOST) ?? ''));
$localHosts = ['localhost', '127.0.0.1', '::1'];

if (in_array($configuredHost, $localHosts, true) && $requestHostName !== '' && !in_array($requestHostName, $localHosts, true)) {
    $forwardedProtocol = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
    $protocol = in_array($forwardedProtocol, ['http', 'https'], true)
        ? $forwardedProtocol
        : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
    $basePath = (string) ($configuredUrlParts['path'] ?? '');
    $appConfig['url'] = $protocol . '://' . $requestHost . rtrim($basePath, '/');
    $GLOBALS['app_config'] = $appConfig;
}

$GLOBALS['url_generator'] = new Url((string) $appConfig['url']);

date_default_timezone_set((string) $appConfig['timezone']);
ErrorHandler::register($appConfig);
SessionManager::start($appConfig);

$requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$basePath = (string) (parse_url((string) $appConfig['url'], PHP_URL_PATH) ?? '');
if ($basePath !== '' && ($requestPath === $basePath || str_starts_with($requestPath, rtrim($basePath, '/') . '/'))) {
    $requestPath = substr($requestPath, strlen(rtrim($basePath, '/'))) ?: '/';
}
$isPublicLanding = PHP_SAPI !== 'cli' && trim($requestPath, '/') === '';
$skipPublicDatabase = $isPublicLanding && is_file(landing_schedule_cache_file());

try {
    if ($skipPublicDatabase) {
        $pdo = null;
    } else {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;sslmode=%s',
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['sslmode'],
        );
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        $pdo->prepare("SELECT set_config('TimeZone', :timezone, false)")->execute(['timezone' => $appConfig['timezone']]);
    }
} catch (PDOException $exception) {
    if (!$isPublicLanding) {
        throw $exception;
    }

    $pdo = null;
}
