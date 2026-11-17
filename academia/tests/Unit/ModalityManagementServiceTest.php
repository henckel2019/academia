<?php

declare(strict_types=1);

namespace Tests\Unit;

use Academia\Repositories\ModalityManagementRepositoryInterface;
use Academia\Services\ModalityManagementService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ModalityManagementServiceTest extends TestCase
{
    public function testAllowsStartOnlyAndPreservesLessonNote(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::once())->method('createModality')->with(
            ['nome' => 'Muay Thai', 'descricao' => '', 'ativo' => true],
            [['dia_semana' => 2, 'horario_inicio' => '20:00', 'horario_fim' => null, 'ativo' => true, 'observacao' => 'Fundamentos de Boxe']]
        )->willReturn(12);
        self::assertSame(12, (new ModalityManagementService($repository))->create([
            'nome' => 'Muay Thai', 'horarios' => [['dia_semana' => 2, 'horario_inicio' => '20:00', 'observacao' => 'Fundamentos de Boxe']],
        ]));
    }

    public function testCreatesModalityWithWeeklySchedule(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('createModality')
            ->with(
                [
                    'nome' => 'Boxe',
                    'descricao' => 'Técnica e condicionamento.',
                    'ativo' => true,
                ],
                [
                    [
                        'dia_semana' => 1,
                        'horario_inicio' => '19:00',
                        'horario_fim' => '20:00',
                        'ativo' => true,
                    ],
                    [
                        'dia_semana' => 3,
                        'horario_inicio' => '19:00',
                        'horario_fim' => '20:00',
                        'ativo' => true,
                    ],
                ]
            )
            ->willReturn(10);

        $id = (new ModalityManagementService($repository))->create([
            'nome' => ' Boxe ',
            'descricao' => 'Técnica e condicionamento.',
            'horarios' => [
                ['dia_semana' => '1', 'horario_inicio' => '19:00', 'horario_fim' => '20:00'],
                ['dia_semana' => '3', 'horario_inicio' => '19:00', 'horario_fim' => '20:00'],
            ],
        ]);

        self::assertSame(10, $id);
    }

    public function testRejectsInvalidWeeklyDay(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::never())->method('createModality');

        $this->expectException(InvalidArgumentException::class);

        (new ModalityManagementService($repository))->create([
            'nome' => 'Boxe',
            'horarios' => [[
                'dia_semana' => '8',
                'horario_inicio' => '19:00',
                'horario_fim' => '20:00',
            ]],
        ]);
    }

    public function testRejectsScheduleWhenEndIsNotAfterStart(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::never())->method('createModality');

        $this->expectException(InvalidArgumentException::class);

        (new ModalityManagementService($repository))->create([
            'nome' => 'Boxe',
            'horarios' => [[
                'dia_semana' => '1',
                'horario_inicio' => '20:00',
                'horario_fim' => '19:00',
            ]],
        ]);
    }

    public function testRejectsDuplicatedScheduleSlot(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::never())->method('createModality');

        $this->expectException(InvalidArgumentException::class);

        (new ModalityManagementService($repository))->create([
            'nome' => 'Boxe',
            'horarios' => [
                ['dia_semana' => '1', 'horario_inicio' => '19:00', 'horario_fim' => '20:00'],
                ['dia_semana' => '1', 'horario_inicio' => '19:00', 'horario_fim' => '20:00'],
            ],
        ]);
    }

    public function testUpdatesModalityAndItsSchedule(): void
    {
        $repository = $this->createMock(ModalityManagementRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('updateModality')
            ->with(
                10,
                [
                    'nome' => 'Boxe Avançado',
                    'descricao' => '',
                ],
                [[
                    'dia_semana' => 5,
                    'horario_inicio' => '18:30',
                    'horario_fim' => '20:00',
                    'ativo' => true,
                ]]
            );

        (new ModalityManagementService($repository))->update(10, [
            'nome' => 'Boxe Avançado',
            'horarios' => [[
                'dia_semana' => '5',
                'horario_inicio' => '18:30',
                'horario_fim' => '20:00',
            ]],
        ]);
    }
}
