<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Repositories\StudentManagementRepositoryInterface;
use Academia\Services\StudentManagementService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StudentManagementServiceTest extends TestCase
{
    public function testCreatesStudentWithGeneratedRegistrationCodeAndIndividualBilling(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('nextRegistrationCode')
            ->willReturn('GA-20260831-A1B2');
        $repository->expects(self::once())
            ->method('createStudent')
            ->with(
                [
                    'nome' => 'Ana Souza',
                    'cpf' => '111.222.333-44',
                    'email' => 'ana@example.com',
                    'telefone' => '(41) 99999-0000',
                    'data_nascimento' => '1995-04-10',
                    'matricula_codigo' => 'GA-20260831-A1B2',
                    'data_matricula' => '2026-08-31',
                    'situacao' => 'ATIVO',
                    'ativo' => true,
                ],
                [
                    [
                        'modalidade_id' => 2,
                        'dia_vencimento' => 12,
                        'valor_plano' => '149.90',
                        'data_inicio' => '2026-08-31',
                        'situacao' => 'ATIVA',
                    ],
                ]
            )
            ->willReturn(30);

        $id = (new StudentManagementService($repository))->create([
            'nome' => ' Ana Souza ',
            'cpf' => '111.222.333-44',
            'email' => 'ana@example.com',
            'telefone' => '(41) 99999-0000',
            'data_nascimento' => '1995-04-10',
            'matricula_codigo' => '',
            'data_matricula' => '2026-08-31',
            'matriculas' => [
                [
                    'modalidade_id' => '2',
                    'dia_vencimento' => '12',
                    'valor_plano' => '149.90',
                    'data_inicio' => '2026-08-31',
                ],
            ],
        ]);

        self::assertSame(30, $id);
    }

    public function testRejectsAnEnrollmentWithAnInvalidDueDay(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::never())->method('createStudent');

        $this->expectException(InvalidArgumentException::class);

        (new StudentManagementService($repository))->create([
            'nome' => 'Ana Souza',
            'matriculas' => [[
                'modalidade_id' => '2',
                'dia_vencimento' => '32',
                'valor_plano' => '149.90',
                'data_inicio' => '2026-08-31',
            ]],
        ]);
    }

    public function testInactivatesAStudentWithoutDeletingTheRecord(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('setStatus')
            ->with(30, 'SUSPENSO', false);

        (new StudentManagementService($repository))->inactivate(30);
    }

    public function testActivatesAnInactiveStudent(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('setStatus')
            ->with(30, 'ATIVO', true);

        (new StudentManagementService($repository))->activate(30);
    }

    public function testUpdatesStudentAndIndividualBillingWithoutChangingTheRegistrationCode(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('updateStudent')
            ->with(
                30,
                [
                    'nome' => 'Ana Souza Atualizada',
                    'cpf' => '',
                    'email' => '',
                    'telefone' => '',
                    'data_nascimento' => null,
                    'matricula_codigo' => 'GA-20260831-A1B2',
                    'data_matricula' => '2026-08-31',
                ],
                [
                    [
                        'id' => 7,
                        'modalidade_id' => 3,
                        'dia_vencimento' => 20,
                        'valor_plano' => '189.50',
                        'data_inicio' => '2026-09-01',
                        'situacao' => 'ATIVA',
                    ],
                ]
            );

        (new StudentManagementService($repository))->update(30, [
            'nome' => 'Ana Souza Atualizada',
            'matricula_codigo' => 'GA-20260831-A1B2',
            'data_matricula' => '2026-08-31',
            'matriculas' => [[
                'id' => '7',
                'modalidade_id' => '3',
                'dia_vencimento' => '20',
                'valor_plano' => '189.50',
                'data_inicio' => '2026-09-01',
            ]],
        ]);
    }

    public function testDeletesStudentLogically(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())->method('softDelete')->with(30);

        (new StudentManagementService($repository))->delete(30);
    }

    public function testCreatesPortalAccessWhenEmailAndInitialPasswordAreProvided(): void
    {
        $repository = $this->createMock(StudentManagementRepositoryInterface::class);
        $repository->expects(self::once())->method('nextRegistrationCode')->willReturn('GA-20260831-C3D4');
        $repository->expects(self::once())
            ->method('createStudent')
            ->with(
                self::isType('array'),
                self::isType('array'),
                ['email' => 'ana@example.com', 'password' => 'SenhaAluno123']
            )
            ->willReturn(31);

        $id = (new StudentManagementService($repository))->create([
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'senha_portal' => 'SenhaAluno123',
        ]);

        self::assertSame(31, $id);
    }
}
