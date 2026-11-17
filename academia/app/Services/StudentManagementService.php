<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\StudentManagementRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;

final class StudentManagementService
{
    public function __construct(private readonly StudentManagementRepositoryInterface $repository)
    {
    }

    public function newPortalPassword(): string
    {
        return 'GA' . strtoupper(bin2hex(random_bytes(4)));
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): int
    {
        $this->validateStudent($input);

        $registrationCode = trim((string) ($input['matricula_codigo'] ?? ''));
        if ($registrationCode === '') {
            $registrationCode = $this->repository->nextRegistrationCode();
        }

        $student = [
            'nome' => trim((string) ($input['nome'] ?? '')),
            'cpf' => trim((string) ($input['cpf'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'telefone' => trim((string) ($input['telefone'] ?? '')),
            'data_nascimento' => $this->nullableString($input['data_nascimento'] ?? null),
            'matricula_codigo' => $registrationCode,
            'data_matricula' => trim((string) ($input['data_matricula'] ?? date('Y-m-d'))),
            'situacao' => 'ATIVO',
            'ativo' => true,
        ];

        if (array_key_exists('whatsapp_cobranca', $input)) {
            $student['whatsapp_cobranca'] = filter_var($input['whatsapp_cobranca'], FILTER_VALIDATE_BOOL) ? 1 : 0;
        }

        $enrollments = $this->normalizeEnrollments($input['matriculas'] ?? []);
        $portalAccess = $this->portalAccess($input);
        if ($portalAccess === null) {
            return $this->repository->createStudent($student, $enrollments);
        }

        return $this->repository->createStudent($student, $enrollments, $portalAccess);
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): void
    {
        $this->validateStudent($input);

        $registrationCode = trim((string) ($input['matricula_codigo'] ?? ''));
        if ($registrationCode === '') {
            $registrationCode = $this->repository->nextRegistrationCode();
        }

        $student = [
            'nome' => trim((string) ($input['nome'] ?? '')),
            'cpf' => trim((string) ($input['cpf'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'telefone' => trim((string) ($input['telefone'] ?? '')),
            'data_nascimento' => $this->nullableString($input['data_nascimento'] ?? null),
            'matricula_codigo' => $registrationCode,
            'data_matricula' => trim((string) ($input['data_matricula'] ?? date('Y-m-d'))),
        ];

        if (array_key_exists('whatsapp_cobranca', $input)) {
            $student['whatsapp_cobranca'] = filter_var($input['whatsapp_cobranca'], FILTER_VALIDATE_BOOL) ? 1 : 0;
        }

        $enrollments = $this->normalizeEnrollments($input['matriculas'] ?? []);
        $portalAccess = $this->portalAccess($input);
        if ($portalAccess === null) {
            $this->repository->updateStudent($id, $student, $enrollments);
            return;
        }

        $this->repository->updateStudent($id, $student, $enrollments, $portalAccess);
    }

    public function inactivate(int $id): void
    {
        $this->repository->setStatus($id, 'SUSPENSO', false);
    }

    public function activate(int $id): void
    {
        $this->repository->setStatus($id, 'ATIVO', true);
    }

    public function delete(int $id): void
    {
        $this->repository->softDelete($id);
    }

    /** @param array<string, mixed> $input */
    private function validateStudent(array $input): void
    {
        if (trim((string) ($input['nome'] ?? '')) === '') {
            throw new InvalidArgumentException('Informe o nome do aluno.');
        }

        if (filter_var($input['whatsapp_cobranca'] ?? false, FILTER_VALIDATE_BOOL)) {
            \Academia\Support\PhoneNumber::brazil((string) ($input['telefone'] ?? ''));
        }
        $email = trim((string) ($input['email'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        $registrationDate = trim((string) ($input['data_matricula'] ?? date('Y-m-d')));
        if (!$this->isDate($registrationDate)) {
            throw new InvalidArgumentException('Informe uma data de matrícula válida.');
        }
    }

    private function isDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    /** @param array<string, mixed> $input @return array{email: string, password: string}|null */
    private function portalAccess(array $input): ?array
    {
        $email = trim((string) ($input['email'] ?? ''));
        $password = trim((string) ($input['senha_portal'] ?? ''));
        if ($password === '') {
            return null;
        }
        if ($email === '') {
            throw new InvalidArgumentException('Informe um e-mail para criar o acesso do aluno.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('A senha do portal deve ter pelo menos 8 caracteres.');
        }

        return ['email' => $email, 'password' => $password];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /** @return list<array<string, mixed>> */
    private function normalizeEnrollments(mixed $rawInput): array
    {
        $enrollments = [];
        $modalities = [];
        $rows = is_array($rawInput) ? $rawInput : [];

        foreach ($rows as $enrollment) {
            if (!is_array($enrollment) || (int) ($enrollment['modalidade_id'] ?? 0) === 0) {
                continue;
            }

            $modalityId = (int) $enrollment['modalidade_id'];
            $dueDay = filter_var($enrollment['dia_vencimento'] ?? null, FILTER_VALIDATE_INT);
            $amount = str_replace(',', '.', trim((string) ($enrollment['valor_plano'] ?? '')));
            $startDate = trim((string) ($enrollment['data_inicio'] ?? ''));
            if ($dueDay === false || $dueDay < 1 || $dueDay > 31) {
                throw new InvalidArgumentException('O dia de vencimento deve estar entre 1 e 31.');
            }
            if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
                throw new InvalidArgumentException('Informe um valor mensal válido.');
            }
            if (!$this->isDate($startDate)) {
                throw new InvalidArgumentException('Informe uma data de início válida.');
            }
            if (isset($modalities[$modalityId])) {
                throw new InvalidArgumentException('Não é possível repetir a mesma modalidade no aluno.');
            }
            $modalities[$modalityId] = true;

            $normalized = [
                'modalidade_id' => $modalityId,
                'dia_vencimento' => $dueDay,
                'valor_plano' => $amount,
                'data_inicio' => $startDate,
                'situacao' => 'ATIVA',
            ];
            $enrollmentId = (int) ($enrollment['id'] ?? 0);
            if ($enrollmentId > 0) {
                $normalized['id'] = $enrollmentId;
            }
            $enrollments[] = $normalized;
        }

        return $enrollments;
    }
}
