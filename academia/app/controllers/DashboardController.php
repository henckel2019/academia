<?php
// app/controllers/DashboardController.php
use Academia\Repositories\DashboardRepository;
use Academia\Services\DashboardService;

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DashboardController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();

        $dashboard = (new DashboardService(new DashboardRepository($this->pdo)))->summary();
        $stats = $dashboard['stats'];
        $financeiro = $dashboard['financeiro'];
        $competencia = $dashboard['competencia'];
        $competenciaLabel = $dashboard['competencia_label'];
        $proximaCompetenciaLabel = $dashboard['proxima_competencia_label'];
        $mensalidadesPagas = $dashboard['mensalidades_pagas'];
        $mensalidadesVencidas = $dashboard['mensalidades_vencidas'];

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
