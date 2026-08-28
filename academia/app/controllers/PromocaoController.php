<?php
// app/controllers/PromocaoController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PromocaoController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();
        $promocao = new Promocao($this->pdo);
        $promocoes = $promocao->all('nome ASC');
        require __DIR__ . '/../views/promocoes/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'tipo' => strtoupper((string)($_POST['tipo'] ?? 'DESCONTO_PCT')),
                'valor' => (float)($_POST['valor'] ?? 0),
                'meses_inicio' => (int)($_POST['meses_inicio'] ?? 0),
                'meses_fim' => !empty($_POST['meses_fim']) ? (int)$_POST['meses_fim'] : null,
            ];
            (new Promocao($this->pdo))->create($dados);
            header('Location: /academia/public/promocoes');
            exit;
        }
        require __DIR__ . '/../views/promocoes/form.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAuth();
        $promocao = new Promocao($this->pdo);
        $item = $promocao->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'tipo' => strtoupper((string)($_POST['tipo'] ?? 'DESCONTO_PCT')),
                'valor' => (float)($_POST['valor'] ?? 0),
                'meses_inicio' => (int)($_POST['meses_inicio'] ?? 0),
                'meses_fim' => !empty($_POST['meses_fim']) ? (int)$_POST['meses_fim'] : null,
            ];
            $promocao->update($id, $dados);
            header('Location: /academia/public/promocoes');
            exit;
        }

        require __DIR__ . '/../views/promocoes/form.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAuth();
        (new Promocao($this->pdo))->delete($id);
        header('Location: /academia/public/promocoes');
        exit;
    }
}
