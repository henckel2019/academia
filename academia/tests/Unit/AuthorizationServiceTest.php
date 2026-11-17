<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Core\HttpException;
use Academia\Services\AuthorizationService;
use Academia\Support\Role;
use PHPUnit\Framework\TestCase;

final class AuthorizationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testAllowsTheAssignedBackendRole(): void
    {
        $_SESSION['auth'] = ['user_id' => 10, 'role' => Role::FINANCE];

        AuthorizationService::requireAnyRole([Role::ADMIN, Role::FINANCE]);

        self::assertTrue(true);
    }

    public function testRejectsAStudentFromFinancialRoles(): void
    {
        $_SESSION['auth'] = ['user_id' => 11, 'role' => Role::STUDENT];

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(403);
        AuthorizationService::requireAnyRole([Role::ADMIN, Role::FINANCE]);
    }
}
