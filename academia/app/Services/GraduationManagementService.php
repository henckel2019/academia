<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\GraduationManagementRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;

final class GraduationManagementService
{
    public function __construct(private readonly GraduationManagementRepositoryInterface $repository)
    {
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): int
    {
        $studentId = (int) ($input['aluno_id'] ?? 0);
        $modalityId = (int) ($input['modalidade_id'] ?? 0);
        $belt = trim((string) ($input['faixa'] ?? ''));
        $grade = filter_var($input['grau'] ?? 0, FILTER_VALIDATE_INT);
        $graduationDate = trim((string) ($input['data_graduacao'] ?? date('Y-m-d')));
        $dueDate = trim((string) ($input['data_vencimento'] ?? date('Y-m-d')));
        $amount = $this->normalizeAmount($input['valor'] ?? '');

        if ($studentId < 1) {
            throw new InvalidArgumentException('Selecione o aluno da graduação.');
        }
        if ($modalityId < 1) {
            throw new InvalidArgumentException('Selecione a modalidade da graduação.');
        }
        if ($belt === '') {
            throw new InvalidArgumentException('Informe a faixa ou graduação conquistada.');
        }
        if (mb_strlen($belt) > 50) {
            throw new InvalidArgumentException('A faixa deve ter no máximo 50 caracteres.');
        }
        if ($grade === false || $grade < 0) {
            throw new InvalidArgumentException('Informe um grau válido.');
        }
        if (!$this->isDate($graduationDate)) {
            throw new InvalidArgumentException('Informe uma data de graduação válida.');
        }
        if (!$this->isDate($dueDate)) {
            throw new InvalidArgumentException('Informe uma data de vencimento válida.');
        }

        $graduation = [
            'aluno_id' => $studentId,
            'modalidade_id' => $modalityId,
            'faixa' => $belt,
            'grau' => (int) $grade,
            'valor' => $amount,
            'data_graduacao' => $graduationDate,
            'professor_id' => $this->nullablePositiveInt($input['professor_id'] ?? null),
            'observacoes' => trim((string) ($input['observacoes'] ?? '')),
        ];
        $charge = [
            'valor' => $amount,
            'data_vencimento' => $dueDate,
            'descricao' => 'Cobrança de graduação - ' . $belt,
        ];

        return $this->repository->createGraduation($graduation, $charge);
    }

    private function normalizeAmount(mixed $value): string
    {
        $amount = str_replace(',', '.', trim((string) $value));
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Informe um valor de graduação válido.');
        }

        [$whole, $decimal] = array_pad(explode('.', $amount, 2), 2, '0');
        if (preg_match('/^0+$/', $whole) === 1 && preg_match('/^0+$/', $decimal) === 1) {
            throw new InvalidArgumentException('O valor da graduação deve ser maior que zero.');
        }

        return (ltrim($whole, '0') ?: '0') . '.' . str_pad(substr($decimal, 0, 2), 2, '0');
    }

    private function isDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
