<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class DashboardRepository implements DashboardRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function stats(string $competencia): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM alunos WHERE situacao = 'ATIVO' AND ativo = TRUE AND deleted_at IS NULL) AS alunos_ativos,
                (SELECT COUNT(*) FROM turmas WHERE ativo = TRUE AND deleted_at IS NULL) AS turmas_ativas,
                (SELECT COUNT(*) FROM mensalidades WHERE competencia = :competencia AND status = 'PAGA') AS mensalidades_pagas,
                (SELECT COUNT(*) FROM mensalidades WHERE competencia = :competencia AND status = 'VENCIDA') AS mensalidades_vencidas"
        );
        $statement->execute(['competencia' => $competencia]);

        $stats = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($stats) ? $stats : [];
    }

    /** @return array<string, mixed> */
    public function financialSummary(string $competencia): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COALESCE((SELECT SUM(valor_final) FROM mensalidades WHERE competencia = :competencia AND status <> 'CANCELADA'), 0) AS faturamento_mes,
                COALESCE((SELECT SUM(valor_final) FROM mensalidades WHERE competencia = :competencia AND status = 'PAGA'), 0) AS recebido_mes,
                COALESCE((
                    SELECT SUM(m.valor_plano)
                    FROM matriculas m
                    INNER JOIN alunos a ON a.id = m.aluno_id
                    WHERE m.situacao = 'ATIVA'
                      AND a.situacao = 'ATIVO'
                      AND a.ativo = TRUE
                      AND a.deleted_at IS NULL
                ), 0) AS previsao_proximo_mes"
        );
        $statement->execute(['competencia' => $competencia]);

        $summary = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($summary) ? $summary : [];
    }

    /** @return list<array<string, mixed>> */
    public function invoicesByStatus(string $competencia, string $status): array
    {
        $statement = $this->pdo->prepare(
            'SELECT mensalidades.id, mensalidades.competencia, mensalidades.valor_final,
                    mensalidades.data_vencimento, mensalidades.data_pagamento, mensalidades.status,
                    alunos.nome AS aluno_nome, modalidades.nome AS modalidade_nome
             FROM mensalidades
             INNER JOIN matriculas ON matriculas.id = mensalidades.matricula_id
             INNER JOIN alunos ON alunos.id = matriculas.aluno_id
             INNER JOIN modalidades ON modalidades.id = matriculas.modalidade_id
             WHERE mensalidades.competencia = :competencia
               AND mensalidades.status = :status
               AND alunos.deleted_at IS NULL
             ORDER BY alunos.nome ASC, mensalidades.data_vencimento ASC'
        );
        $statement->execute(['competencia' => $competencia, 'status' => $status]);
        $invoices = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($invoices) ? $invoices : [];
    }
}
