<?php
// app/views/layouts/main.php
$appName = 'Academia Garagem Aço';
$perfil = $_SESSION['perfil'] ?? '';
$usuario = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; echo $appName; ?></title>
    <link rel="stylesheet" href="/academia/public/css/app.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><?php echo $appName; ?></div>
        <nav>
            <a href="/academia/public/dashboard">Dashboard</a>
            <a href="/academia/public/alunos">Alunos</a>
            <a href="/academia/public/modalidades">Modalidades</a>
            <a href="/academia/public/turmas">Turmas</a>
            <a href="/academia/public/graduacoes">Graduações</a>
            <a href="/academia/public/professores">Professores</a>
            <a href="/academia/public/frequencia">Frequência</a>
            <a href="/academia/public/biometria">Biometria</a>
            <a href="/academia/public/financeiro">Financeiro</a>
            <a href="/academia/public/promocoes">Promoções</a>
            <a href="/academia/public/usuarios">Usuários</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <?php echo htmlspecialchars($usuario['nome'] ?? ''); ?><br>
                <small><?php echo htmlspecialchars($perfil); ?></small>
            </div>
            <a href="/academia/public/logout" class="logout">Sair</a>
        </div>
    </aside>

    <main class="content">
        <?php if (isset($content)) echo $content; ?>
    </main>
</body>
</html>
