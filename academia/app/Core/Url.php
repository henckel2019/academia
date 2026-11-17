<?php

declare(strict_types=1);

namespace Academia\Core;

final class Url
{
    private string $baseUrl;

    private string $basePath;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $path = parse_url($this->baseUrl, PHP_URL_PATH);
        $this->basePath = $path === null || $path === '/' ? '' : rtrim($path, '/');
    }

    public function to(string $path = '/'): string
    {
        if ($path === '' || $path === '/') {
            return $this->baseUrl . '/';
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    public function asset(string $path): string
    {
        return $this->to($path);
    }

    public function basePath(): string
    {
        return $this->basePath;
    }
}
