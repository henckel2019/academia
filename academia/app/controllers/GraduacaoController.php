<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use Academia\Repositories\GraduationManagementRepository;
use Academia\Services\GraduationManagementService;

final class GraduacaoController
{
    private GraduationManagementRepository $repository;
    private GraduationManagementService $service;

    public function __construct(PDO $pdo)
    {
        $this->repository = new GraduationManagementRepository($pdo);
        $this->service = new GraduationManagementService($this->repository);
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $lista = $this->repository->list();
        $message = flash('success');
        $error = flash('error');
        require __DIR__ . '/../views/graduacoes/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();
        $alunos = $this->repository->activeStudents();
        $modalidades = $this->repository->activeModalities();
        $professores = $this->repository->activeProfessors();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $item = [];
            require __DIR__ . '/../views/graduacoes/form.php';
            return;
        }

        try {
            $this->service->create($_POST);
            flash('success', 'Graduação registrada e cobrança gerada para o aluno.');
            redirect('/graduacoes');
        } catch (\InvalidArgumentException $exception) {
            $item = $_POST;
            $error = $exception->getMessage();
            require __DIR__ . '/../views/graduacoes/form.php';
        } catch (\Throwable $exception) {
            flash('error', 'Não foi possível registrar a graduação e gerar a cobrança.');
            redirect('/graduacoes');
        }
    }
}
