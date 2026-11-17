<?php

declare(strict_types=1);
use Academia\Repositories\CheckinRepository;
use Academia\Repositories\SettingsRepository;
use Academia\Services\CheckinService;

final class CheckinController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function index(): void
    {
        $result = $_SESSION['checkin_result'] ?? null;
        unset($_SESSION['checkin_result']);
        $query = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $query = trim((string) ($_POST['identificador'] ?? ''));
            $studentId = filter_var($_POST['aluno_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $origin = ($_POST['origem'] ?? '') === 'BIOMETRIA' ? 'BIOMETRIA' : 'WEB';
            $result = (new CheckinService(new CheckinRepository($this->pdo), new SettingsRepository($this->pdo)))
                ->checkin($query, $studentId === false ? null : $studentId, (int) $_SESSION['auth']['user_id'], $origin);
            if (($_POST['response'] ?? '') === 'json') {
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-store');
                echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                return;
            }
            $_SESSION['checkin_result'] = $result;
            redirect('/checkin');
        }
        require __DIR__ . '/../views/checkin/index.php';
    }
}
