<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class StudentPortalRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{student: array<string, mixed>|null, invoices: list<array<string, mixed>>, enrollments: list<array<string, mixed>>, summary: array<string, mixed>} */
    public function dashboardForUser(int $userId): array
    {
        $studentStatement = $this->pdo->prepare(
            'SELECT id, nome, email, matricula_codigo, situacao
             FROM alunos
             WHERE usuario_id = :user_id AND deleted_at IS NULL
             LIMIT 1'
        );
        $studentStatement->execute(['user_id' => $userId]);
        $student = $studentStatement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($student)) {
            return [
                'student' => null,
                'invoices' => [],
                'enrollments' => [],
                'summary' => ['atrasadas' => 0, 'abertas' => 0, 'futuras' => 0, 'total_em_aberto' => '0.00'],
            ];
        }

        $enrollmentStatement = $this->pdo->prepare(
            "SELECT matriculas.id, matriculas.valor_plano, matriculas.dia_vencimento,
                    matriculas.data_inicio, matriculas.situacao, modalidades.nome AS modalidade_nome
             FROM matriculas
             INNER JOIN modalidades ON modalidades.id = matriculas.modalidade_id
             WHERE matriculas.aluno_id = :student_id
               AND matriculas.situacao IN ('ATIVA', 'SUSPENSA')
             ORDER BY modalidades.nome ASC"
        );
        $enrollmentStatement->execute(['student_id' => (int) $student['id']]);
        $enrollments = $enrollmentStatement->fetchAll(PDO::FETCH_ASSOC);

        $invoiceStatement = $this->pdo->prepare(
            "SELECT mensalidades.competencia, mensalidades.tipo, mensalidades.descricao, mensalidades.valor_final, mensalidades.data_vencimento,
                    mensalidades.data_pagamento, mensalidades.status, mensalidades.pix_copia_e_cola,
                    modalidades.nome AS modalidade_nome
             FROM mensalidades
             INNER JOIN matriculas ON matriculas.id = mensalidades.matricula_id
             INNER JOIN modalidades ON modalidades.id = matriculas.modalidade_id
             WHERE matriculas.aluno_id = :student_id
             ORDER BY CASE mensalidades.status WHEN 'VENCIDA' THEN 0 WHEN 'ABERTA' THEN 1 ELSE 2 END,
                      mensalidades.data_vencimento ASC, mensalidades.competencia DESC
             LIMIT 24"
        );
        $invoiceStatement->execute(['student_id' => (int) $student['id']]);
        $invoices = $invoiceStatement->fetchAll(PDO::FETCH_ASSOC);

        $summaryStatement = $this->pdo->prepare(
            "SELECT
                COUNT(*) FILTER (WHERE status = 'VENCIDA' OR (status = 'ABERTA' AND data_vencimento < CURRENT_DATE)) AS atrasadas,
                COUNT(*) FILTER (WHERE status = 'ABERTA' AND data_vencimento >= CURRENT_DATE AND data_vencimento < (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month')) AS abertas,
                COUNT(*) FILTER (WHERE status = 'ABERTA' AND data_vencimento >= (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month')) AS futuras,
                COALESCE(SUM(CASE WHEN status IN ('ABERTA', 'VENCIDA') THEN valor_final ELSE 0 END), 0) AS total_em_aberto
             FROM mensalidades
             INNER JOIN matriculas ON matriculas.id = mensalidades.matricula_id
             WHERE matriculas.aluno_id = :student_id"
        );
        $summaryStatement->execute(['student_id' => (int) $student['id']]);
        $summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);

        return [
            'student' => $student,
            'invoices' => is_array($invoices) ? $invoices : [],
            'enrollments' => is_array($enrollments) ? $enrollments : [],
            'summary' => is_array($summary) ? $summary : ['atrasadas' => 0, 'abertas' => 0, 'futuras' => 0, 'total_em_aberto' => '0.00'],
        ];
    }
}
