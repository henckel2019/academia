<?php

declare(strict_types=1);
use Academia\Repositories\CheckinRepository;

final class BiometriaController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function index(): void
    {
        $students = (new CheckinRepository($this->pdo))->students();
        require __DIR__ . '/../views/biometria/index.php';
    }
}
