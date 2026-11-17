<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\CheckinRepository;
use Academia\Repositories\SettingsRepository;
use DateTimeImmutable;

final class CheckinService
{
    public function __construct(private readonly CheckinRepository $repository, private readonly SettingsRepository $settings)
    {
    }

    public function checkin(string $identifier, ?int $studentId, int $userId, string $origin): array
    {
        $student = $this->repository->findByIdentifier($identifier, $studentId);
        if ($student === null) {
            return ['title' => 'ALUNO NÃO IDENTIFICADO', 'message' => 'Use a matrícula ou CPF. Nomes repetidos precisam de um identificador único.', 'allowed' => false];
        }
        $settings = $this->settings->all();
        $decision = (new CheckinAccessService())->decide(
            $student['situacao'],
            $student['matricula_situacao'] ?? 'CANCELADA',
            $student['mensalidade_status'],
            $student['data_vencimento'],
            (int) ($settings['dias_tolerancia_financeira'] ?? 5),
            (bool) ($settings['permitir_bloqueio_inadimplencia'] ?? true),
            new DateTimeImmutable('today')
        );
        $message = $decision->message;
        if ($decision->allowed) {
            $registered = $this->repository->register((int) $student['id'], (int) $student['matricula_id'], $userId, $origin);
            $message .= $registered ? ' Presença registrada.' : ' Presença já registrada hoje nesta modalidade.';
        }
        return ['title' => $decision->title, 'message' => $message, 'allowed' => $decision->allowed, 'student' => $student['nome']];
    }
}
