<?php

declare(strict_types=1);

use Academia\Core\Csrf;
use Academia\Core\Request;
use Academia\Core\SessionManager;
use Academia\Repositories\AuditRepository;
use Academia\Repositories\LoginAttemptRepository;
use Academia\Repositories\UserRepository;
use Academia\Services\AuthenticationService;
use Academia\Support\Role;

final class AuthController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function login(): void
    {
        if (!empty($_SESSION['auth']['user_id'])) {
            redirect($_SESSION['auth']['role'] === Role::STUDENT ? '/portal/dashboard' : '/dashboard');
        }

        $error = null;
        if (Request::method() === 'POST') {
            Csrf::verifyRequest();
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['senha'] ?? '');
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === '') {
                $error = 'E-mail ou senha inválidos.';
            } else {
                $service = new AuthenticationService(
                    new UserRepository($this->pdo), new LoginAttemptRepository($this->pdo), new AuditRepository($this->pdo),
                    (int) app_config('login_max_attempts'), (int) app_config('login_lockout_seconds')
                );
                $user = $service->authenticate($email, $password, Request::clientIp());
                if ($user === null) {
                    $error = 'E-mail ou senha inválidos, ou o acesso está temporariamente bloqueado.';
                } else {
                    SessionManager::regenerate();
                    $_SESSION['auth'] = [
                        'user_id' => (int) $user['id'], 'name' => (string) $user['nome'], 'email' => (string) $user['email'],
                        'role' => (string) $user['role'], 'first_access' => (bool) $user['primeiro_acesso'],
                    ];
                    redirect($user['role'] === Role::STUDENT ? '/portal/dashboard' : '/dashboard');
                }
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        if (Request::method() !== 'POST') {
            abort(405, 'Método não permitido.');
        }
        Csrf::verifyRequest();
        $userId = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : null;
        if ($userId !== null) {
            (new AuditRepository($this->pdo))->record($userId, 'LOGOUT', 'usuarios', $userId);
        }
        SessionManager::destroy();
        redirect('/login');
    }
}
