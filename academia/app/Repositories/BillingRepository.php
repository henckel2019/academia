<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;
use InvalidArgumentException;
use Throwable;

final class BillingRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function list(string $month, string $search): array
    {
        $statement = $this->pdo->prepare("SELECT b.*, a.nome aluno_nome, a.matricula_codigo, md.nome modalidade_nome,
            CASE WHEN b.status='ABERTA' AND b.data_vencimento < CURRENT_DATE THEN 'VENCIDA' ELSE b.status END situacao_exibicao
            FROM mensalidades b JOIN matriculas m ON m.id=b.matricula_id JOIN alunos a ON a.id=m.aluno_id
            JOIN modalidades md ON md.id=m.modalidade_id
            WHERE b.competencia=:mes AND (a.nome ILIKE :busca OR a.matricula_codigo ILIKE :codigo)
            ORDER BY b.data_vencimento, a.nome, b.id");
        $statement->execute(['mes' => $month, 'busca' => '%' . $search . '%', 'codigo' => '%' . $search . '%']);
        return $statement->fetchAll();
    }

    public function generate(string $month, ?int $userId): int
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare("INSERT INTO mensalidades (matricula_id, competencia, valor, valor_final, data_vencimento)
                SELECT m.id, :competencia, m.valor_plano, m.valor_plano,
                    (date_trunc('month', CAST(:data AS date))::date +
                    (LEAST(m.dia_vencimento, EXTRACT(DAY FROM (date_trunc('month', CAST(:fim AS date)) + INTERVAL '1 month - 1 day'))::int)-1))
                FROM matriculas m JOIN alunos a ON a.id=m.aluno_id
                WHERE m.situacao='ATIVA' AND a.situacao='ATIVO' AND a.deleted_at IS NULL
                  AND m.data_inicio < CAST(:proximo AS date) + INTERVAL '1 month'
                  AND (m.data_fim IS NULL OR m.data_fim >= CAST(:inicio AS date))
                ON CONFLICT (matricula_id, competencia) WHERE tipo='MENSALIDADE' DO NOTHING");
            $first = $month . '-01';
            $statement->execute(['competencia' => $month, 'data' => $first, 'fim' => $first, 'proximo' => $first, 'inicio' => $first]);
            $count = $statement->rowCount();
            if ($count > 0) {
                (new AuditRepository($this->pdo))->record($userId, 'GERAR_MENSALIDADES', 'mensalidades', null, null, ['competencia' => $month, 'quantidade' => $count]);
            }
            $this->pdo->commit();
            return $count;
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }

    public function markOverdue(): int
    {
        return $this->pdo->exec("UPDATE mensalidades SET status='VENCIDA', updated_at=CURRENT_TIMESTAMP WHERE status='ABERTA' AND data_vencimento < CURRENT_DATE");
    }

    public function settle(int $billId, string $method, int $userId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare('SELECT * FROM mensalidades WHERE id=:id FOR UPDATE');
            $statement->execute(['id' => $billId]);
            $bill = $statement->fetch();
            if (!$bill || $bill['status'] === 'CANCELADA') {
                throw new InvalidArgumentException('Cobrança inexistente ou cancelada.');
            }
            if ($bill['status'] === 'PAGA') {
                $this->pdo->commit();
                return false;
            }
            if (preg_match('/[1-9]/', $bill['valor_final']) === 1) {
                $payment = $this->pdo->prepare("INSERT INTO pagamentos (mensalidade_id,tipo,valor_pago,data_pagamento,confirmado,confirmado_por_usuario_id,confirmado_em)
                    VALUES (:id,:tipo,:valor,CURRENT_TIMESTAMP,TRUE,:usuario,CURRENT_TIMESTAMP)");
                $payment->execute(['id' => $billId, 'tipo' => $method, 'valor' => $bill['valor_final'], 'usuario' => $userId]);
            }
            $update = $this->pdo->prepare("UPDATE mensalidades SET status='PAGA', data_pagamento=CURRENT_DATE, updated_at=CURRENT_TIMESTAMP WHERE id=:id");
            $update->execute(['id' => $billId]);
            $after = ['status' => 'PAGA', 'tipo' => $method, 'valor' => $bill['valor_final']];
            (new AuditRepository($this->pdo))->record($userId, 'CONFIRMAR_PAGAMENTO', 'mensalidades', $billId, $bill, $after);
            $history = $this->pdo->prepare("INSERT INTO financeiro_historico (mensalidade_id,usuario_id,acao,dados_antes,dados_depois)
                VALUES (:id,:usuario,'CONFIRMAR_PAGAMENTO',CAST(:antes AS jsonb),CAST(:depois AS jsonb))");
            $history->execute(['id' => $billId, 'usuario' => $userId, 'antes' => json_encode($bill, JSON_THROW_ON_ERROR), 'depois' => json_encode($after, JSON_THROW_ON_ERROR)]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
