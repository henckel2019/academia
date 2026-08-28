<?php
// app/controllers/TurmaController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class TurmaController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();
        $turma = new Turma($this->pdo);
        $lista = $turma->all('nome ASC');
        $modalidades = (new Modalidade($this->pdo))->all();
        require __DIR__ . '/../views/turmas/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'modalidade_id' => (int)($_POST['modalidade_id'] ?? 0),
                'professor_id' => !empty($_POST['professor_id']) ? (int)$_POST['professor_id'] : null,
                'capacidade' => (int)($_POST['capacidade'] ?? 0),
                'dias_semana' => trim((string)($_POST['dias_semana'] ?? '')),
                'horario_inicio' => !empty($_POST['horario_inicio']) ? $_POST['horario_inicio'] : null,
                'horario_fim' => !empty($_POST['horario_fim']) ? $_POST['horario_fim'] : null,
                'unidade' => trim((string)($_POST['unidade'] ?? '')),
            ];
            (new Turma($this->pdo))->create($dados);
            header('Location: /academia/public/turmas');
            exit;
        }

        $modalidades = (new Modalidade($this->pdo))->all();
        $professores = (new Professor($this->pdo))->all();
        require __DIR__ . '/../views/turmas/form.php';
    }

    public function edit(int $id): void {
        AuthMiddleware::requireAuth();
        $turma = new Turma($this->pdo);
        $item = $turma->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim((string)($_POST['nome'] ?? '')),
                'modalidade_id' => (int)($_POST['modalidade_id'] ?? 0),
                'professor_id' => !empty($_POST['professor_id']) ? (int)$_POST['professor_id'] : null,
                'capacidade' => (int)($_POST['capacidade'] ?? 0),
                'dias_semana' => trim((string)($_POST['dias_semana'] ?? '')),
                'horario_inicio' => !empty($_POST['horario_inicio']) ? $_POST['horario_inicio'] : null,
                'horario_fim' => !empty($_POST['horario_fim']) ? $_POST['horario_fim'] : null,
                'unidade' => trim((string)($_POST['unidade'] ?? '')),
            ];
            $turma->update($id, $dados);
            header('Location: /academia/public/turmas');
            exit;
        }

        $modalidades = (new Modalidade($this->pdo))->all();
        $professores = (new Professor($this->pdo))->all();
        require __DIR__ . '/../views/turmas/form.php';
    }

    public function delete(int $id): void {
        AuthMiddleware::requireAuth();
        (new Turma($this->pdo))->delete($id);
        header('Location: /academia/public/turmas');
        exit;
    }
}
