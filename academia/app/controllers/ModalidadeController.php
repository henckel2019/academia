<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use Academia\Repositories\ModalityManagementRepository;
use Academia\Services\ModalityManagementService;

final class ModalidadeController
{
    private ModalityManagementRepository $repository;
    private ModalityManagementService $service;

    public function __construct(PDO $pdo)
    {
        $this->repository = new ModalityManagementRepository($pdo);
        $this->service = new ModalityManagementService($this->repository);
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $lista = $this->repository->list();
        $message = flash('success');
        $error = flash('error');
        require __DIR__ . '/../views/modalidades/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $item = null;
            $horarios = [];
            require __DIR__ . '/../views/modalidades/form.php';
            return;
        }

        try {
            $this->service->create($_POST);
            flash('success', 'Modalidade cadastrada com sucesso.');
            redirect('/modalidades');
        } catch (\InvalidArgumentException $exception) {
            $item = $_POST;
            $horarios = is_array($_POST['horarios'] ?? null) ? $_POST['horarios'] : [];
            $error = $exception->getMessage();
            require __DIR__ . '/../views/modalidades/form.php';
        } catch (\Throwable $exception) {
            flash('error', 'Não foi possível salvar a modalidade. Verifique se o nome já está cadastrado.');
            redirect('/modalidades');
        }
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();
        $record = $this->repository->find($id);
        if ($record === null) {
            abort(404, 'Modalidade não encontrada.');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $item = $record['modality'];
            $horarios = $record['schedules'];
            require __DIR__ . '/../views/modalidades/form.php';
            return;
        }

        try {
            $this->service->update($id, $_POST);
            flash('success', 'Modalidade atualizada com sucesso.');
            redirect('/modalidades');
        } catch (\InvalidArgumentException $exception) {
            $item = $_POST;
            $item['id'] = $id;
            $horarios = is_array($_POST['horarios'] ?? null) ? $_POST['horarios'] : [];
            $error = $exception->getMessage();
            require __DIR__ . '/../views/modalidades/form.php';
        } catch (\Throwable $exception) {
            flash('error', 'Não foi possível atualizar a modalidade. Verifique se o nome já está cadastrado.');
            redirect('/modalidades');
        }
    }

    public function activate(int $id): void
    {
        AuthMiddleware::requireAuth();
        $this->service->activate($id);
        flash('success', 'Modalidade ativada e disponível na landing page.');
        redirect('/modalidades');
    }

    public function inactivate(int $id): void
    {
        AuthMiddleware::requireAuth();
        $this->service->inactivate($id);
        flash('success', 'Modalidade inativada e removida da landing page.');
        redirect('/modalidades');
    }

    public function delete(int $id): void
    {
        $this->inactivate($id);
    }
}
