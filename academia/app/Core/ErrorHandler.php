<?php

declare(strict_types=1);

namespace Academia\Core;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    /** @var array<string, mixed> */
    private static array $config = [];

    public static function register(array $config): void
    {
        self::$config = $config;
        $debug = (bool) ($config['debug'] ?? false);
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $exception): void {
            self::render($exception);
        });
    }

    public static function render(Throwable $exception): never
    {
        $status = $exception instanceof HttpException ? $exception->statusCode : 500;
        $message = $exception instanceof HttpException && $exception->getMessage() !== ''
            ? $exception->getMessage()
            : match ($status) {
                403 => 'Você não tem permissão para acessar este recurso.',
                404 => 'A página solicitada não foi encontrada.',
                419 => 'Sua sessão expirou. Atualize a página e tente novamente.',
                default => 'Não foi possível concluir esta operação agora. Tente novamente mais tarde.',
            };

        error_log(sprintf('[%s] %s in %s:%d', (string) $status, $exception->getMessage(), $exception->getFile(), $exception->getLine()));

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $message . PHP_EOL);
            exit(1);
        }

        http_response_code($status);
        $title = $status === 500 ? 'Erro interno' : 'Solicitação não concluída';
        $showDetails = (bool) (self::$config['debug'] ?? false) && !($exception instanceof HttpException);
        $details = $showDetails ? $exception->getMessage() : null;
        require dirname(__DIR__) . '/views/errors/error.php';
        exit;
    }
}
