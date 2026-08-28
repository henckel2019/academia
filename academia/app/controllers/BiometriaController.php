<?php
// app/controllers/BiometriaController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class BiometriaController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();
        require __DIR__ . '/../views/biometria/index.php';
    }
}
