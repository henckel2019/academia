<?php
// database/seed.php - cria banco e admin padrão
$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host={$host}", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("CREATE DATABASE IF NOT EXISTS academia_garagem_aco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE academia_garagem_aco");

$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);

$senhaHash = password_hash('senha@123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil_id) VALUES (:nome, :email, :senha_hash, :perfil_id)");
$stmt->execute([
    'nome' => 'Administrador',
    'email' => 'admin@academia.local',
    'senha_hash' => $senhaHash,
    'perfil_id' => 1,
]);

echo "Banco criado e usuário administrador inserido com sucesso.\nLogin: admin@academia.local / senha@123\n";
