<?php

declare(strict_types=1);
use Academia\Repositories\BillingRepository;
use Academia\Services\BillingService;

final class FinanceiroController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function index(): void
    {
        $repository = new BillingRepository($this->pdo);
        $service = new BillingService($repository);
        $month = (string) ($_GET['mes'] ?? date('Y-m'));
        $search = trim((string) ($_GET['busca'] ?? ''));
        $notice = $_SESSION['billing_notice'] ?? null;
        unset($_SESSION['billing_notice']);
        $error = null;
        try {
            BillingService::validateMonth($month);
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $userId = (int) $_SESSION['auth']['user_id'];
                $action = (string) ($_POST['acao'] ?? '');
                if ($action === 'gerar') {
                    $count = $service->generate($month, $userId);
                    $notice = "{$count} mensalidade(s) gerada(s). Cobranças existentes foram preservadas.";
                } elseif ($action === 'pagar') {
                    $changed = $service->settle((int) ($_POST['mensalidade_id'] ?? 0), (string) ($_POST['tipo'] ?? ''), $userId);
                    $notice = $changed ? 'Pagamento confirmado. O acesso será reavaliado no próximo check-in.' : 'Este pagamento já estava confirmado.';
                } else {
                    throw new InvalidArgumentException('Ação inválida.');
                }
                $_SESSION['billing_notice'] = $notice;
                redirect('/financeiro?mes=' . rawurlencode($month));
            }
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
            $month = date('Y-m');
        }
        $bills = $repository->list($month, $search);
        require __DIR__ . '/../views/financeiro/index.php';
    }
}
