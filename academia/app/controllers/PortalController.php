<?php

declare(strict_types=1);

use Academia\Repositories\StudentPortalRepository;

final class PortalController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function dashboard(): void
    {
        $userId = (int) ($_SESSION['auth']['user_id'] ?? 0);
        $portal = (new StudentPortalRepository($this->pdo))->dashboardForUser($userId);
        $student = $portal['student'];
        $invoices = $portal['invoices'];
        $enrollments = $portal['enrollments'];
        $portalSummary = $portal['summary'];
        $user = $_SESSION['auth'] ?? [];

        require __DIR__ . '/../views/portal/dashboard.php';
    }
}
