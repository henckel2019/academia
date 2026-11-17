<?php

declare(strict_types=1);

namespace Academia\Repositories;

use InvalidArgumentException;
use PDO;
use Throwable;

final class GraduationManagementRepository implements GraduationManagementRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        $statement = $this->pdo->query(
            "SELECT g.id, g.aluno_id, g.modalidade_id, g.faixa, g.grau, g.valor, g.data_graduacao,
                    g.observacoes, a.nome AS aluno_nome, m.nome AS modalidade_nome,
                    p.nome AS professor_nome, c.id AS cobranca_id, c.status AS cobranca_status,
                    c.data_vencimento AS cobranca_data_vencimento
             FROM graduacoes g
             INNER JOIN alunos a ON a.id = g.aluno_id
             INNER JOIN modalidades m ON m.id = g.modalidade_id
             LEFT JOIN professores p ON p.id = g.professor_id
             LEFT JOIN mensalidades c ON c.graduacao_id = g.id
             ORDER BY g.data_graduacao DESC, g.id DESC"
        );
        $graduations = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($graduations) ? $graduations : [];
    }

    /** @return list<array<string, mixed>> */
    public function activeStudents(): array
    {
        $statement = $this->pdo->query(
            "SELECT id, nome
             FROM alunos
             WHERE ativo = TRUE AND situacao = 'ATIVO' AND deleted_at IS NULL
             ORDER BY nome ASC"
        );
        $students = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($students) ? $students : [];
    }

    /** @return list<array<string, mixed>> */
    public function activeModalities(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, nome
             FROM modalidades
             WHERE ativo = TRUE AND deleted_at IS NULL
             ORDER BY nome ASC'
        );
        $modalities = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($modalities) ? $modalities : [];
    }

    /** @return list<array<string, mixed>> */
    public function activeProfessors(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, nome
             FROM professores
             WHERE ativo = TRUE AND deleted_at IS NULL
             ORDER BY nome ASC'
        );
        $professors = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($professors) ? $professors : [];
    }

    /** @param array<string, mixed> $graduation @param array<string, mixed> $charge */
    public function createGraduation(array $graduation, array $charge): int
    {
        $this->pdo->beginTransaction();
        try {
            $enrollmentStatement = $this->pdo->prepare(
                "SELECT id
                 FROM matriculas
                 WHERE aluno_id = :aluno_id
                   AND modalidade_id = :modalidade_id
                   AND situacao IN ('ATIVA', 'SUSPENSA')
                 ORDER BY data_inicio DESC, id DESC
                 LIMIT 1"
            );
            $enrollmentStatement->execute([
                'aluno_id' => $graduation['aluno_id'],
                'modalidade_id' => $graduation['modalidade_id'],
            ]);
            $enrollmentId = $enrollmentStatement->fetchColumn();
            if ($enrollmentId === false) {
                throw new InvalidArgumentException('O aluno não possui matrícula ativa nesta modalidade.');
            }

            $graduationStatement = $this->pdo->prepare(
                'INSERT INTO graduacoes (aluno_id, modalidade_id, faixa, grau, valor, data_graduacao, professor_id, observacoes)
                 VALUES (:aluno_id, :modalidade_id, :faixa, :grau, :valor, :data_graduacao, :professor_id, NULLIF(:observacoes, \'\'))
                 RETURNING id'
            );
            $graduationStatement->execute($graduation);
            $graduationId = (int) $graduationStatement->fetchColumn();

            $invoiceStatement = $this->pdo->prepare(
                "INSERT INTO mensalidades
                    (matricula_id, tipo, descricao, graduacao_id, competencia, valor, valor_desconto, valor_final, data_vencimento, status)
                 VALUES
                    (:matricula_id, 'GRADUACAO', :descricao, :graduacao_id, :competencia, :valor, 0, :valor_final, :data_vencimento, 'ABERTA')
                 RETURNING id"
            );
            $invoiceStatement->execute([
                'matricula_id' => (int) $enrollmentId,
                'descricao' => $charge['descricao'],
                'graduacao_id' => $graduationId,
                'competencia' => substr((string) $charge['data_vencimento'], 0, 7),
                'valor' => $charge['valor'],
                'valor_final' => $charge['valor'],
                'data_vencimento' => $charge['data_vencimento'],
            ]);
            $invoiceStatement->fetchColumn();
            $this->pdo->commit();

            return $graduationId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
