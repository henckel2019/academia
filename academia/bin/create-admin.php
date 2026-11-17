<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

function prompt(string $label, bool $hidden = false): string
{
    if ($hidden && PHP_OS_FAMILY !== 'Windows') {
        fwrite(STDOUT, $label);
        shell_exec('stty -echo');
        $value = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);

        return $value;
    }

    fwrite(STDOUT, $label);

    return trim((string) fgets(STDIN));
}

$name = prompt('Nome: ');
$email = strtolower(prompt('E-mail: '));
$password = prompt('Senha (será exibida no Windows): ', true);

if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($password) < 12) {
    fwrite(STDERR, "Informe nome, e-mail válido e senha com ao menos 12 caracteres.\n");
    exit(1);
}

$role = $pdo->prepare("SELECT id FROM perfis WHERE nome = 'SUPER_ADMIN'");
$role->execute();
$roleId = $role->fetchColumn();
if ($roleId === false) {
    fwrite(STDERR, "Perfis não encontrados. Execute php bin/migrate.php antes deste comando.\n");
    exit(1);
}

$exists = $pdo->prepare('SELECT 1 FROM usuarios WHERE LOWER(email) = LOWER(:email)');
$exists->execute(['email' => $email]);
if ($exists->fetchColumn()) {
    fwrite(STDERR, "Já existe um usuário com este e-mail.\n");
    exit(1);
}

$insert = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil_id, primeiro_acesso, senha_alterada_em) VALUES (:nome, :email, :senha_hash, :perfil_id, FALSE, CURRENT_TIMESTAMP)');
$insert->execute([
    'nome' => $name,
    'email' => $email,
    'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
    'perfil_id' => $roleId,
]);

echo "SUPER_ADMIN criado com sucesso.\n";
