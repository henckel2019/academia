<?php
// public/login.php
$dbConfig = require __DIR__ . '/../app/config/database.php';
$appConfig = require __DIR__ . '/../app/config/app.php';

session_start();
date_default_timezone_set($appConfig['timezone'] ?? 'America/Sao_Paulo');

$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset={$dbConfig['charset']}";
$options = $dbConfig['options'] ?? [];
$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
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

        $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW(), primeiro_acesso = 0 WHERE id = :id")->execute(['id' => $user['id']]);

        header('Location: /academia/public/dashboard');
        exit;
    } else {
        $error = 'Email ou senha inválidos.';
    }
}

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo htmlspecialchars($appConfig['app_name']); ?></title>
    <link rel="stylesheet" href="/academia/public/css/app.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1><?php echo htmlspecialchars($appConfig['app_name']); ?></h1>
        <p class="subtitle">Acesso administrativo</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <label>Email
                <input type="email" name="email" required>
            </label>
            <label>Senha
                <input type="password" name="senha" required>
            </label>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>
