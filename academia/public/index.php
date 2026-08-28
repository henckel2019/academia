<?php
// public/index.php
$dbConfig = require __DIR__ . '/../app/config/database.php';
$appConfig = require __DIR__ . '/../app/config/app.php';

session_start();
date_default_timezone_set($appConfig['timezone'] ?? 'America/Sao_Paulo');

$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset={$dbConfig['charset']}";
$options = $dbConfig['options'] ?? [];
$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);

$requestUri = '/';
if (!empty($_SERVER['REQUEST_URI'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
} elseif (!empty($argc) && !empty($argv[1])) {
    $requestUri = $argv[1];
}

$parsed = parse_url($requestUri);
$path = $parsed['path'] ?? '/';
$path = str_replace('\\', '/', $path);

$publicBase = str_replace('\\', '/', realpath(__DIR__));
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$scriptBase = dirname($scriptName);

if ($scriptBase && $publicBase && $scriptBase !== $publicBase && basename($scriptName) !== 'index.php') {
    $path = ltrim(str_replace($scriptBase, '', $path), '/');
} elseif (str_starts_with($path, $publicBase)) {
    $path = ltrim(substr($path, strlen($publicBase)), '/');
} else {
    $path = ltrim($path, '/');
}

$path = urldecode($path);

$prefix = '/academia/public';
if (str_starts_with($path, $prefix)) {
    $path = ltrim(substr($path, strlen($prefix)), '/');
}

$prefix2 = 'academia/public';
if (str_starts_with($path, $prefix2)) {
    $path = ltrim(substr($path, strlen($prefix2)), '/');
}

if ($path === '' || $path === 'index.php') {
    header('Location: /academia/public/dashboard');
    exit;
}

spl_autoload_register(function ($class) {
    $map = [
        'BaseModel' => __DIR__ . '/../app/models/BaseModel.php',
        'Aluno' => __DIR__ . '/../app/models/Aluno.php',
        'Modalidade' => __DIR__ . '/../app/models/Modalidade.php',
        'Turma' => __DIR__ . '/../app/models/Turma.php',
        'Graduacao' => __DIR__ . '/../app/models/Graduacao.php',
        'Frequencia' => __DIR__ . '/../app/models/Frequencia.php',
        'Usuario' => __DIR__ . '/../app/models/Usuario.php',
        'Mensalidade' => __DIR__ . '/../app/models/Mensalidade.php',
        'Plano' => __DIR__ . '/../app/models/Plano.php',
        'Matricula' => __DIR__ . '/../app/models/Matricula.php',
        'Promocao' => __DIR__ . '/../app/models/Promocao.php',
        'Professor' => __DIR__ . '/../app/models/Professor.php',
    ];
    if (isset($map[$class]) && file_exists($map[$class])) {
        require_once $map[$class];
    }
});

require __DIR__ . '/../app/routes/web.php';
