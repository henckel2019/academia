<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function testNormalizesBrazilianPhonesWithOrWithoutCountryCode(): void
    {
        self::assertSame('5541999213112', PhoneNumber::brazil('(41) 99921-3112'));
        self::assertSame('5541999213112', PhoneNumber::brazil('+55 41 99921-3112'));
        self::assertSame('5555999213112', PhoneNumber::brazil('(55) 99921-3112'));
    }

    public function testRejectsPhoneWithoutAreaCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PhoneNumber::brazil('99921-3112');
    }
}
