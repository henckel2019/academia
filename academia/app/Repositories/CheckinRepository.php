<?php

declare(strict_types=1);

namespace Academia\Repositories;

use PDO;

final class CheckinRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function students(): array
    {
        return $this->pdo->query("SELECT id, nome, matricula_codigo FROM alunos WHERE deleted_at IS NULL ORDER BY nome")->fetchAll();
    }

    public function findByIdentifier(string $value, ?int $studentId = null): ?array
    {
        // An old unpaid bill must never be hidden by a newer paid/future bill.
        $condition = $studentId !== null ? 'a.id = :id' : "(LOWER(a.nome)=LOWER(:nome) OR a.matricula_codigo=:codigo OR regexp_replace(a.cpf, '[^0-9]', '', 'g')=NULLIF(:cpf, ''))";
        $sql = "SELECT a.id, a.nome, a.situacao, m.id matricula_id, m.situacao matricula_situacao,
                b.status mensalidade_status, b.data_vencimento
            FROM alunos a
            LEFT JOIN LATERAL (SELECT * FROM matriculas WHERE aluno_id=a.id
                AND data_inicio <= CURRENT_DATE AND (data_fim IS NULL OR data_fim >= CURRENT_DATE)
                ORDER BY (situacao='ATIVA') DESC, data_inicio DESC, id DESC LIMIT 1) m ON TRUE
            LEFT JOIN LATERAL (SELECT c.* FROM mensalidades c JOIN matriculas cm ON cm.id=c.matricula_id
                WHERE cm.aluno_id=a.id AND c.status IN ('ABERTA','VENCIDA')
                ORDER BY c.data_vencimento, c.id LIMIT 1) b ON TRUE
            WHERE a.deleted_at IS NULL AND {$condition} ORDER BY a.id LIMIT 2";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($studentId !== null ? ['id' => $studentId] : ['nome' => $value, 'codigo' => $value, 'cpf' => preg_replace('/\D/', '', $value)]);
        $rows = $statement->fetchAll();
        return count($rows) === 1 ? $rows[0] : null;
    }

    public function register(int $studentId, int $enrollmentId, int $userId, string $origin): bool
    {
        $statement = $this->pdo->prepare("INSERT INTO frequencias (aluno_id, modalidade_id, data, hora_entrada, origem, registrado_por_usuario_id)
            SELECT :aluno, modalidade_id, CURRENT_DATE, CURRENT_TIME, :origem, :usuario FROM matriculas
            WHERE id=:matricula ON CONFLICT (aluno_id, modalidade_id, data) WHERE origem IN ('WEB','BIOMETRIA') DO NOTHING");
        $statement->execute(['aluno' => $studentId, 'origem' => $origin, 'usuario' => $userId, 'matricula' => $enrollmentId]);
        return $statement->rowCount() === 1;
    }
}
