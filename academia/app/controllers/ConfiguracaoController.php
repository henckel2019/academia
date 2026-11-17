<?php

declare(strict_types=1);

use Academia\Repositories\AuditRepository;
use Academia\Repositories\SettingsRepository;
use Academia\Services\SettingsService;

final class ConfiguracaoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function index(): void
    {
        $repository = new SettingsRepository($this->pdo);
        $error = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $values = (new SettingsService())->validate($_POST);
                $repository->save($values, (int) $_SESSION['auth']['user_id']);
                (new AuditRepository($this->pdo))->record((int) $_SESSION['auth']['user_id'], 'CONFIGURACOES_ATUALIZADAS', 'configuracoes', null, null, $values);
                flash('success', 'Configurações salvas com sucesso.');
                redirect('/configuracoes');
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }
        $settings = $repository->all();
        require __DIR__ . '/../views/configuracoes/index.php';
    }
}
