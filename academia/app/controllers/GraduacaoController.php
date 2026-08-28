<?php
// app/controllers/GraduacaoController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class GraduacaoController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();

        $sql = "SELECT g.*, a.nome as aluno_nome, m.nome as modalidade_nome
                FROM graduacoes g
                JOIN alunos a ON a.id = g.aluno_id
                JOIN modalidades m ON m.id = g.modalidade_id
                ORDER BY g.data_graduacao DESC";
        $lista = $this->pdo->query($sql)->fetchAll();

        require __DIR__ . '/../views/graduacoes/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'aluno_id' => (int)($_POST['aluno_id'] ?? 0),
                'modalidade_id' => (int)($_POST['modalidade_id'] ?? 0),
                'faixa' => trim((string)($_POST['faixa'] ?? '')),
                'grau' => (int)($_POST['grau'] ?? 0),
                'data_graduacao' => !empty($_POST['data_graduacao']) ? $_POST['data_graduacao'] : date('Y-m-d'),
                'professor_responsavel' => trim((string)($_POST['professor_responsavel'] ?? '')),
                'observacoes' => trim((string)($_POST['observacoes'] ?? '')),
            ];
            (new Graduacao($this->pdo))->create($dados);
            header('Location: /academia/public/graduacoes');
            exit;
        }

        $alunos = (new Aluno($this->pdo))->all();
        $modalidades = (new Modalidade($this->pdo))->all();

        require __DIR__ . '/../views/graduacoes/form.php';
    }
}
