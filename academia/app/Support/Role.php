<?php

declare(strict_types=1);

namespace Academia\Support;

final class Role
{
    public const SUPER_ADMIN = 'SUPER_ADMIN';
    public const ADMIN = 'ADMIN';
    public const FINANCE = 'FINANCE';
    public const INSTRUCTOR = 'INSTRUCTOR';
    public const RECEPTION = 'RECEPTION';
    public const STUDENT = 'STUDENT';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN, self::FINANCE, self::INSTRUCTOR, self::RECEPTION, self::STUDENT];
    }
}
