<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Core\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function testBuildsLocalUrlsWithoutHardcodingTheProjectPath(): void
    {
        $url = new Url('http://localhost/academia/public');

        self::assertSame('http://localhost/academia/public/login', $url->to('/login'));
        self::assertSame('/academia/public', $url->basePath());
    }

    public function testBuildsProductionUrlsAtDomainRoot(): void
    {
        $url = new Url('https://academia.exemplo.com');

        self::assertSame('https://academia.exemplo.com/portal/dashboard', $url->to('portal/dashboard'));
        self::assertSame('', $url->basePath());
    }
}
