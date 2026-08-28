<?php
// app/controllers/ModalidadeController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ModalidadeController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();
        $modalidade = new Modalidade($this->pdo);
        $lista = $modalidade->all('nome ASC');
        require __DIR__ . '/../views/modalidades/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = ['nome' => trim((string)($_POST['nome'] ?? '')), 'descricao' => trim((string)($_POST['descricao'] ?? ''))];
            (new Modalidade($this->pdo))->create($dados);
            header('Location: /academia/public/modalidades');
            exit;
        }
        require __DIR__ . '/../views/modalidades/form.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAuth();
        $modalidade = new Modalidade($this->pdo);
        $item = $modalidade->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = ['nome' => trim((string)($_POST['nome'] ?? '')), 'descricao' => trim((string)($_POST['descricao'] ?? ''))];
            $modalidade->update($id, $dados);
            header('Location: /academia/public/modalidades');
            exit;
        }

        require __DIR__ . '/../views/modalidades/form.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAuth();
        (new Modalidade($this->pdo))->delete($id);
        header('Location: /academia/public/modalidades');
        exit;
    }
}
