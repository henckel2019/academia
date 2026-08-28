<?php
// app/controllers/ProfessorController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ProfessorController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();
        $professores = (new Professor($this->pdo))->all();
        require __DIR__ . '/../views/professores/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'telefone' => trim((string)($_POST['telefone'] ?? '')),
                'cref' => trim((string)($_POST['cref'] ?? '')),
            ];
            (new Professor($this->pdo))->create($dados);
            header('Location: /academia/public/professores');
            exit;
        }
        require __DIR__ . '/../views/professores/form.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAuth();
        $professor = new Professor($this->pdo);
        $item = $professor->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'telefone' => trim((string)($_POST['telefone'] ?? '')),
                'cref' => trim((string)($_POST['cref'] ?? '')),
            ];
            $professor->update($id, $dados);
            header('Location: /academia/public/professores');
            exit;
        }

        require __DIR__ . '/../views/professores/form.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAuth();
        (new Professor($this->pdo))->delete($id);
        header('Location: /academia/public/professores');
        exit;
    }
}
