<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string)($_POST['email'] ?? ''));
            $senha = (string)($_POST['senha'] ?? '');
            $usuarioModel = new Usuario($this->pdo);
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($senha, $user['senha_hash'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'nome' => (string)$user['nome'],
                    'email' => (string)$user['email'],
                    'perfil' => (string)$user['perfil_id'],
                ];
                $_SESSION['perfil'] = (string)$user['perfil_id'];

                $this->pdo->prepare("UPDATE usuarios SET ultimo_login = NOW(), primeiro_acesso = 0 WHERE id = :id")->execute(['id' => $user['id']]);

                header('Location: /academia/public/dashboard');
                exit;
            } else {
                $error = 'Email ou senha inválidos.';
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: /academia/public/login.php');
        exit;
    }
}
