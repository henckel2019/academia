<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class WhatsAppBillingRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function enqueue(int $leadDays): int
    {
        $statement = $this->pdo->prepare("INSERT INTO whatsapp_cobrancas(mensalidade_id)
            SELECT b.id FROM mensalidades b JOIN matriculas m ON m.id=b.matricula_id JOIN alunos a ON a.id=m.aluno_id
            WHERE b.tipo='MENSALIDADE' AND b.status IN ('ABERTA','VENCIDA') AND b.valor_final>0
              AND b.competencia BETWEEN TO_CHAR(CURRENT_DATE,'YYYY-MM') AND TO_CHAR(CURRENT_DATE + CAST(:janela AS int),'YYYY-MM')
              AND b.data_vencimento <= CURRENT_DATE + CAST(:dias AS int)
              AND a.whatsapp_cobranca=TRUE AND a.deleted_at IS NULL AND a.situacao='ATIVO'
              AND m.situacao='ATIVA' AND m.data_inicio<=CURRENT_DATE AND (m.data_fim IS NULL OR m.data_fim>=CURRENT_DATE)
            ON CONFLICT(mensalidade_id) DO NOTHING");
        $statement->execute(['dias' => $leadDays, 'janela' => $leadDays]);
        return $statement->rowCount();
    }

    public function pendingIds(): array
    {
        return $this->pdo->query("SELECT id FROM whatsapp_cobrancas WHERE status='PENDENTE' ORDER BY id LIMIT 25")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function lock(int $id): ?array
    {
        $statement = $this->pdo->prepare("SELECT o.*,b.status bill_status,b.data_vencimento,b.competencia,b.valor_final,
            a.nome aluno_nome,a.telefone current_phone,a.whatsapp_cobranca,a.situacao student_status,a.deleted_at,
            m.situacao enrollment_status,m.data_inicio,m.data_fim,md.nome modalidade_nome
            FROM whatsapp_cobrancas o JOIN mensalidades b ON b.id=o.mensalidade_id
            JOIN matriculas m ON m.id=b.matricula_id JOIN alunos a ON a.id=m.aluno_id
            JOIN modalidades md ON md.id=m.modalidade_id WHERE o.id=:id FOR UPDATE OF b,o,a,m");
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function finish(int $id, string $status, ?string $phone = null, ?string $message = null, ?string $error = null, ?string $externalId = null): void
    {
        $statement = $this->pdo->prepare("UPDATE whatsapp_cobrancas SET status=:status, telefone=:telefone,
            mensagem=:mensagem, erro=:erro, mensagem_externa_id=:externa,
            enviada_em=CASE WHEN :enviada=1 THEN CURRENT_TIMESTAMP ELSE enviada_em END,updated_at=CURRENT_TIMESTAMP WHERE id=:id");
        $statement->execute(['id' => $id, 'status' => $status, 'telefone' => $phone, 'mensagem' => $message, 'erro' => $error, 'externa' => $externalId, 'enviada' => $status === 'ENVIADA' ? 1 : 0]);
    }

    public function history(): array
    {
        return $this->pdo->query("SELECT o.id,o.status,o.telefone,o.erro,o.enviada_em,o.created_at,o.mensagem,
            b.competencia,b.data_vencimento,a.nome aluno_nome FROM whatsapp_cobrancas o
            JOIN mensalidades b ON b.id=o.mensalidade_id JOIN matriculas m ON m.id=b.matricula_id
            JOIN alunos a ON a.id=m.aluno_id ORDER BY o.id DESC LIMIT 100")->fetchAll();
    }

    public function retry(int $id, int $userId): void
    {
        $this->pdo->beginTransaction();
        try {
            $row = $this->lock($id);
            if (!$row || !in_array($row['status'], ['FALHA', 'REVISAO'], true)) {
                throw new \InvalidArgumentException('Esta cobrança não pode ser reenviada.');
            }
            $this->pdo->prepare("UPDATE whatsapp_cobrancas SET status='PENDENTE',chave_envio=gen_random_uuid(),erro=NULL,updated_at=CURRENT_TIMESTAMP WHERE id=:id")->execute(['id' => $id]);
            (new AuditRepository($this->pdo))->record($userId, 'REVISAR_ENVIO_WHATSAPP', 'whatsapp_cobrancas', $id, ['status' => $row['status']], ['status' => 'PENDENTE']);
            $this->pdo->commit();
        } catch (\Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
        }
    }
}
