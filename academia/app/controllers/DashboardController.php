<?php
// app/controllers/DashboardController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DashboardController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();

        $stats = [
            'alunos_ativos' => (int)$this->pdo->query("SELECT COUNT(*) FROM alunos WHERE situacao = 'ATIVO'")->fetchColumn(),
            'turmas_ativas' => (int)$this->pdo->query("SELECT COUNT(*) FROM turmas WHERE ativo = 1")->fetchColumn(),
            'mensalidades_abertas' => (int)$this->pdo->query("SELECT COUNT(*) FROM mensalidades WHERE status = 'ABERTA'")->fetchColumn(),
            'mensalidades_vencidas' => (int)$this->pdo->query("SELECT COUNT(*) FROM mensalidades WHERE status = 'VENCIDA'")->fetchColumn(),
        ];

        $financeiro = $this->pdo->query("SELECT SUM(valor_final) AS faturamento, SUM(CASE WHEN status='PAGA' THEN valor_final ELSE 0 END) AS recebido FROM mensalidades")->fetch();

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
