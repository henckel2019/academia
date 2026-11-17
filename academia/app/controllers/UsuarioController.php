<?php

// app/controllers/UsuarioController.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsuarioController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $sql = "SELECT u.*, p.nome as perfil_nome FROM usuarios u JOIN perfis p ON p.id = u.perfil_id ORDER BY u.nome ASC";
        $lista = $this->pdo->query($sql)->fetchAll();
        $perfis = $this->pdo->query("SELECT * FROM perfis ORDER BY nome ASC")->fetchAll();

        require __DIR__ . '/../views/usuarios/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha = (string) ($_POST['senha'] ?? '');
            if (strlen($senha) < 12 || trim((string) ($_POST['nome'] ?? '')) === '' ||
                filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) === false) {
                abort(422, 'Informe nome, e-mail válido e senha com pelo menos 12 caracteres.');
            }
            $profile = $this->pdo->prepare('SELECT nome FROM perfis WHERE id=:id');
            $profile->execute(['id' => (int) ($_POST['perfil_id'] ?? 0)]);
            $profileName = $profile->fetchColumn();
            if (!$profileName || ($profileName === 'SUPER_ADMIN' && ($_SESSION['auth']['role'] ?? '') !== 'SUPER_ADMIN')) {
                abort(403, 'Você não pode atribuir este perfil.');
            }
            $dados = [
                'nome' => trim((string) ($_POST['nome'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
                'perfil_id' => (int) ($_POST['perfil_id'] ?? 0),
            ];
            (new Usuario($this->pdo))->create($dados);
            redirect('/usuarios');
            exit;
        }
        $perfis = $this->pdo->query("SELECT * FROM perfis ORDER BY nome ASC")->fetchAll();
        require __DIR__ . '/../views/usuarios/form.php';
    }
}
