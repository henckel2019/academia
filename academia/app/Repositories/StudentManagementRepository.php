<?php

declare(strict_types=1);

namespace Academia\Repositories;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class StudentManagementRepository implements StudentManagementRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        $students = $this->pdo->query(
            'SELECT id, usuario_id, nome, cpf, email, telefone, data_nascimento, matricula_codigo,
                    data_matricula, situacao, ativo, whatsapp_cobranca
             FROM alunos
             WHERE deleted_at IS NULL
             ORDER BY nome ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($students) || $students === []) {
            return [];
        }

        $enrollments = $this->pdo->query(
            "SELECT m.aluno_id, m.id, m.modalidade_id, mo.nome AS modalidade_nome,
                    m.valor_plano, m.dia_vencimento, m.data_inicio, m.situacao
             FROM matriculas m
             INNER JOIN modalidades mo ON mo.id = m.modalidade_id
             INNER JOIN alunos a ON a.id = m.aluno_id
             WHERE m.situacao IN ('ATIVA', 'SUSPENSA')
               AND a.deleted_at IS NULL
             ORDER BY m.aluno_id, mo.nome"
        )->fetchAll(PDO::FETCH_ASSOC);

        $byStudent = [];
        foreach (is_array($enrollments) ? $enrollments : [] as $enrollment) {
            $studentId = (int) ($enrollment['aluno_id'] ?? 0);
            $byStudent[$studentId][] = $enrollment;
        }

        foreach ($students as &$student) {
            $student['matriculas'] = $byStudent[(int) $student['id']] ?? [];
        }
        unset($student);

        return $students;
    }

    /** @return array{student: array<string, mixed>, enrollments: list<array<string, mixed>>}|null */
    public function find(int $id): ?array
    {
        $studentStatement = $this->pdo->prepare(
            'SELECT id, usuario_id, nome, cpf, email, telefone, data_nascimento, matricula_codigo,
                    data_matricula, situacao, ativo, whatsapp_cobranca
             FROM alunos
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1'
        );
        $studentStatement->execute(['id' => $id]);
        $student = $studentStatement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($student)) {
            return null;
        }

        $enrollmentStatement = $this->pdo->prepare(
            "SELECT m.id, m.modalidade_id, mo.nome AS modalidade_nome,
                    m.valor_plano, m.dia_vencimento, m.data_inicio, m.situacao
             FROM matriculas m
             INNER JOIN modalidades mo ON mo.id = m.modalidade_id
             WHERE m.aluno_id = :student_id
               AND m.situacao IN ('ATIVA', 'SUSPENSA')
             ORDER BY mo.nome"
        );
        $enrollmentStatement->execute(['student_id' => $id]);
        $enrollments = $enrollmentStatement->fetchAll(PDO::FETCH_ASSOC);

        return [
            'student' => $student,
            'enrollments' => is_array($enrollments) ? $enrollments : [],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function activeModalities(): array
    {
        $modalities = $this->pdo->query(
            'SELECT id, nome, descricao
             FROM modalidades
             WHERE ativo = TRUE AND deleted_at IS NULL
             ORDER BY nome ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        return is_array($modalities) ? $modalities : [];
    }

    public function nextRegistrationCode(): string
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM alunos WHERE matricula_codigo = :code LIMIT 1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = 'GA-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $statement->execute(['code' => $code]);
            if ($statement->fetchColumn() === false) {
                return $code;
            }
        }

        throw new RuntimeException('Não foi possível gerar um código de matrícula único.');
    }

    /** @param array<string, mixed> $student @param list<array<string, mixed>> $enrollments @param array{email: string, password: string}|null $portalAccess */
    public function createStudent(array $student, array $enrollments, ?array $portalAccess = null): int
    {
        $this->pdo->beginTransaction();
        try {
            $studentStatement = $this->pdo->prepare(
                'INSERT INTO alunos (nome, cpf, email, telefone, data_nascimento, matricula_codigo, data_matricula, situacao, ativo, whatsapp_cobranca)
                 VALUES (:nome, NULLIF(:cpf, \'\'), NULLIF(:email, \'\'), NULLIF(:telefone, \'\'), :data_nascimento, :matricula_codigo, :data_matricula, :situacao, :ativo, :whatsapp_cobranca)
                 RETURNING id'
            );
            $studentStatement->execute($student + ['whatsapp_cobranca' => 0]);
            $studentId = (int) $studentStatement->fetchColumn();
            $this->insertEnrollments($studentId, $enrollments);
            if ($portalAccess !== null) {
                $this->savePortalAccess($studentId, $student['nome'], $portalAccess['email'], $portalAccess['password']);
            }
            $this->pdo->commit();

            return $studentId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $student @param list<array<string, mixed>> $enrollments @param array{email: string, password: string}|null $portalAccess */
    public function updateStudent(int $id, array $student, array $enrollments, ?array $portalAccess = null): void
    {
        $this->pdo->beginTransaction();
        try {
            $studentStatement = $this->pdo->prepare(
                'UPDATE alunos
                 SET nome = :nome, cpf = NULLIF(:cpf, \'\'), email = NULLIF(:email, \'\'),
                     whatsapp_cobranca = :whatsapp_cobranca, telefone = NULLIF(:telefone, \'\'), data_nascimento = :data_nascimento,
                     matricula_codigo = :matricula_codigo, data_matricula = :data_matricula,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $studentStatement->execute($student + ['id' => $id, 'whatsapp_cobranca' => 0]);

            $currentStatement = $this->pdo->prepare(
                "SELECT id FROM matriculas WHERE aluno_id = :student_id AND situacao IN ('ATIVA', 'SUSPENSA')"
            );
            $currentStatement->execute(['student_id' => $id]);
            $currentIds = array_map('intval', $currentStatement->fetchAll(PDO::FETCH_COLUMN));
            $keptIds = [];

            $updateEnrollment = $this->pdo->prepare(
                'UPDATE matriculas
                 SET modalidade_id = :modalidade_id, dia_vencimento = :dia_vencimento,
                     valor_plano = :valor_plano, data_inicio = :data_inicio,
                     situacao = :situacao, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND aluno_id = :aluno_id'
            );
            foreach ($enrollments as $enrollment) {
                $enrollmentId = (int) ($enrollment['id'] ?? 0);
                if ($enrollmentId > 0) {
                    if (!in_array($enrollmentId, $currentIds, true)) {
                        throw new InvalidArgumentException('A matrícula informada não pertence a este aluno.');
                    }
                    $updateEnrollment->execute($enrollment + ['aluno_id' => $id]);
                    $keptIds[] = $enrollmentId;
                    continue;
                }

                $this->insertEnrollments($id, [$enrollment]);
            }

            $idsToCancel = array_values(array_diff($currentIds, $keptIds));
            if ($idsToCancel !== []) {
                $placeholders = implode(',', array_fill(0, count($idsToCancel), '?'));
                $cancelStatement = $this->pdo->prepare(
                    "UPDATE matriculas
                     SET situacao = 'CANCELADA', data_fim = COALESCE(data_fim, CURRENT_DATE), updated_at = CURRENT_TIMESTAMP
                     WHERE aluno_id = ? AND id IN ({$placeholders})"
                );
                $cancelStatement->execute(array_merge([$id], $idsToCancel));
            }

            if ($portalAccess !== null) {
                $this->savePortalAccess($id, $student['nome'], $portalAccess['email'], $portalAccess['password']);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function setStatus(int $id, string $status, bool $active): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE alunos
             SET situacao = :situacao, ativo = :ativo, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute(['situacao' => $status, 'ativo' => $active, 'id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE alunos
             SET deleted_at = CURRENT_TIMESTAMP, situacao = 'CANCELADO', ativo = FALSE, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deleted_at IS NULL"
        );
        $statement->execute(['id' => $id]);
    }

    /** @param list<array<string, mixed>> $enrollments */
    private function insertEnrollments(int $studentId, array $enrollments): void
    {
        if ($enrollments === []) {
            return;
        }

        $planId = $this->personalizedPlanId();
        $statement = $this->pdo->prepare(
            'INSERT INTO matriculas (aluno_id, modalidade_id, plano_id, valor_plano, dia_vencimento, data_inicio, situacao)
             VALUES (:aluno_id, :modalidade_id, :plano_id, :valor_plano, :dia_vencimento, :data_inicio, :situacao)'
        );

        foreach ($enrollments as $enrollment) {
            $statement->execute($enrollment + ['aluno_id' => $studentId, 'plano_id' => $planId]);
            if ($statement->rowCount() !== 1) {
                throw new RuntimeException('Não foi possível salvar a matrícula do aluno.');
            }
        }
    }

    private function personalizedPlanId(): int
    {
        $select = $this->pdo->prepare(
            "SELECT id FROM planos WHERE nome = 'Plano personalizado' AND ativo = TRUE AND deleted_at IS NULL LIMIT 1"
        );
        $select->execute();
        $planId = $select->fetchColumn();
        if ($planId !== false) {
            return (int) $planId;
        }

        $insert = $this->pdo->prepare(
            "INSERT INTO planos (nome, valor, dia_vencimento, descricao)
             VALUES ('Plano personalizado', 0, 10, 'Valores e vencimento definidos individualmente na matrícula.')
             RETURNING id"
        );
        $insert->execute();

        return (int) $insert->fetchColumn();
    }

    private function savePortalAccess(int $studentId, string $studentName, string $email, string $password): void
    {
        $studentStatement = $this->pdo->prepare('SELECT usuario_id FROM alunos WHERE id = :id FOR UPDATE');
        $studentStatement->execute(['id' => $studentId]);
        $userId = $studentStatement->fetchColumn();
        if ($userId !== false && $userId !== null) {
            $userStatement = $this->pdo->prepare(
                'UPDATE usuarios
                 SET nome = :nome, email = :email, senha_hash = :senha_hash,
                     primeiro_acesso = TRUE, ativo = TRUE, deleted_at = NULL, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $userStatement->execute([
                'nome' => $studentName,
                'email' => $email,
                'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => (int) $userId,
            ]);

            return;
        }

        $profileStatement = $this->pdo->prepare("SELECT id FROM perfis WHERE nome = 'STUDENT' LIMIT 1");
        $profileStatement->execute();
        $profileId = $profileStatement->fetchColumn();
        if ($profileId === false) {
            throw new RuntimeException('O perfil de aluno não está configurado.');
        }

        $userStatement = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil_id, primeiro_acesso)
             VALUES (:nome, :email, :senha_hash, :perfil_id, TRUE)
             RETURNING id'
        );
        $userStatement->execute([
            'nome' => $studentName,
            'email' => $email,
            'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
            'perfil_id' => (int) $profileId,
        ]);
        $newUserId = (int) $userStatement->fetchColumn();
        $linkStatement = $this->pdo->prepare('UPDATE alunos SET usuario_id = :usuario_id WHERE id = :id');
        $linkStatement->execute(['usuario_id' => $newUserId, 'id' => $studentId]);
    }
}
