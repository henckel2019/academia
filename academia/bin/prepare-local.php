<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli' || getenv('ACADEMIA_LOCAL_SETUP') !== '1' || $dbConfig['host'] !== '127.0.0.1' || (int) $dbConfig['port'] !== 55432 || $appConfig['environment'] !== 'local') {
    fwrite(STDERR, "Este comando é exclusivo da instalação local iniciada por start-local.ps1.\n");
    exit(1);
}
$accessFile = dirname(__DIR__) . '/.local/acesso-local.txt';
if (is_file($accessFile)) {
    echo "Credenciais locais preservadas em .local/acesso-local.txt\n";
    return;
}
$email = 'gestao@garagem.local';
$exists = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(email)=:email');
$exists->execute(['email' => $email]);
if ($exists->fetchColumn()) {
    fwrite(STDERR, "O administrador local já existe. Use sua senha ou crie outro administrador com bin/create-admin.php.\n");
    exit(1);
}
$password = bin2hex(random_bytes(12));
$insert = $pdo->prepare("INSERT INTO usuarios (nome,email,senha_hash,perfil_id,primeiro_acesso)
    SELECT 'Gestão local',:email,:senha,id,FALSE FROM perfis WHERE nome='SUPER_ADMIN'");
$insert->execute(['email' => $email, 'senha' => password_hash($password, PASSWORD_DEFAULT)]);
if ($insert->rowCount() !== 1) {
    throw new RuntimeException('Execute as migrations antes de preparar o acesso.');
}
$directory = dirname($accessFile);
if (!is_dir($directory)) {
    mkdir($directory, 0700, true);
}
$written = file_put_contents($accessFile, "Acesso exclusivo ao banco local de desenvolvimento\nE-mail: {$email}\nSenha: {$password}\n", LOCK_EX);
if ($written === false) {
    throw new RuntimeException('Não foi possível gravar as credenciais locais.');
}
echo "Administrador local criado. Credenciais em .local/acesso-local.txt (fora do Git).\n";
