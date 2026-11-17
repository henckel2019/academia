<?php

declare(strict_types=1);

use Academia\Repositories\StudentManagementRepository;
use Academia\Services\StudentManagementService;

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

final class AlunoController
{
    private StudentManagementRepository $repository;
    private StudentManagementService $service;

    public function __construct(private readonly PDO $pdo)
    {
        $this->repository = new StudentManagementRepository($this->pdo);
        $this->service = new StudentManagementService($this->repository);
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $alunos = $this->repository->list();
        $modalidades = $this->repository->activeModalities();
        $registrationCode = $this->repository->nextRegistrationCode();
        $portalPassword = $this->service->newPortalPassword();
        $message = flash('success');
        $error = flash('error');

        require __DIR__ . '/../views/alunos/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/alunos#novo-aluno');
        }

        try {
            $this->service->create($_POST);
            $this->flashAccessMessage($_POST, 'Aluno cadastrado com sucesso.');
        } catch (\InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/alunos');
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $record = $this->repository->find($id);
        if ($record === null) {
            http_response_code(404);
            echo 'Aluno não encontrado.';
            return;
        }

        $aluno = $record['student'];
        $matriculas = $record['enrollments'];
        $modalidades = $this->repository->activeModalities();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->service->update($id, $_POST);
                $this->flashAccessMessage($_POST, 'Aluno atualizado com sucesso.');
                redirect('/alunos');
            } catch (\InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }

        require __DIR__ . '/../views/alunos/form.php';
    }

    public function inactivate(int $id): void
    {
        AuthMiddleware::requireAuth();
        $this->service->inactivate($id);
        flash('success', 'Aluno inativado.');
        redirect('/alunos');
    }

    public function activate(int $id): void
    {
        AuthMiddleware::requireAuth();
        $this->service->activate($id);
        flash('success', 'Aluno ativado.');
        redirect('/alunos');
    }

    public function delete(int $id): void
    {
        AuthMiddleware::requireAuth();
        $this->service->delete($id);
        flash('success', 'Aluno excluído da lista. O histórico foi preservado.');
        redirect('/alunos');
    }

    /** @param array<string, mixed> $input */
    private function flashAccessMessage(array $input, string $prefix): void
    {
        $email = trim((string) ($input['email'] ?? ''));
        $password = trim((string) ($input['senha_portal'] ?? ''));
        if ($email !== '' && $password !== '') {
            flash('success', $prefix . ' Acesso do portal: ' . $email . ' | Senha inicial: ' . $password . '.');
            return;
        }

        flash('success', $prefix);
    }
}
