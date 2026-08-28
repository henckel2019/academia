<?php
// app/controllers/FrequenciaController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class FrequenciaController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        AuthMiddleware::requireAuth();

        $sql = "SELECT f.*, a.nome as aluno_nome, t.nome as turma_nome
                FROM frequencias f
                JOIN alunos a ON a.id = f.aluno_id
                LEFT JOIN turmas t ON t.id = f.turma_id
                ORDER BY f.data DESC, f.hora_entrada DESC
                LIMIT 200";

        $lista = $this->pdo->query($sql)->fetchAll();

        require __DIR__ . '/../views/frequencia/index.php';
    }

    public function create(): void {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'aluno_id' => (int)($_POST['aluno_id'] ?? 0),
                'turma_id' => !empty($_POST['turma_id']) ? (int)$_POST['turma_id'] : null,
                'modalidade_id' => !empty($_POST['modalidade_id']) ? (int)$_POST['modalidade_id'] : null,
                'data' => !empty($_POST['data']) ? $_POST['data'] : date('Y-m-d'),
                'hora_entrada' => !empty($_POST['hora_entrada']) ? $_POST['hora_entrada'] : date('H:i:s'),
                'hora_saida' => !empty($_POST['hora_saida']) ? $_POST['hora_saida'] : null,
                'origem' => strtoupper((string)($_POST['origem'] ?? 'MANUAL')),
            ];

            (new Frequencia($this->pdo))->create($dados);
            header('Location: /academia/public/frequencia');
            exit;
        }

        $alunos = (new Aluno($this->pdo))->all('nome ASC');
        $turmas = (new Turma($this->pdo))->all();
        $modalidades = (new Modalidade($this->pdo))->all();

        require __DIR__ . '/../views/frequencia/form.php';
    }
}
