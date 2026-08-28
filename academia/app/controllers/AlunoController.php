<?php
// app/controllers/AlunoController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AlunoController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();

        $alunoModel = new Aluno($this->pdo);
        $alunos = $alunoModel->all('nome ASC');

        require __DIR__ . '/../views/alunos/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'cpf' => trim((string)($_POST['cpf'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'telefone' => trim((string)($_POST['telefone'] ?? '')),
                'data_nascimento' => !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null,
                'matricula_codigo' => trim((string)($_POST['matricula_codigo'] ?? '')),
                'data_matricula' => !empty($_POST['data_matricula']) ? $_POST['data_matricula'] : date('Y-m-d'),
                'situacao' => strtoupper((string)($_POST['situacao'] ?? 'ATIVO')),
            ];
            $alunoModel = new Aluno($this->pdo);
            $alunoModel->create($dados);

            header('Location: /academia/public/alunos');
            exit;
        }

        require __DIR__ . '/../views/alunos/form.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAuth();

        $alunoModel = new Aluno($this->pdo);
        $aluno = $alunoModel->find($id);

        if (!$aluno) {
            http_response_code(404);
            echo 'Aluno não encontrado.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'cpf' => trim((string)($_POST['cpf'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'telefone' => trim((string)($_POST['telefone'] ?? '')),
                'data_nascimento' => !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null,
                'situacao' => strtoupper((string)($_POST['situacao'] ?? 'ATIVO')),
            ];
            $alunoModel->update($id, $dados);

            header('Location: /academia/public/alunos');
            exit;
        }

        require __DIR__ . '/../views/alunos/form.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAuth();
        $alunoModel = new Aluno($this->pdo);
        $alunoModel->delete($id);
        header('Location: /academia/public/alunos');
        exit;
    }
}
