<?php

declare(strict_types=1);

return static function (\PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS modalidade_horarios (
            id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
            modalidade_id BIGINT NOT NULL REFERENCES modalidades(id) ON DELETE CASCADE,
            dia_semana SMALLINT NOT NULL,
            horario_inicio TIME NOT NULL,
            horario_fim TIME NOT NULL,
            ativo BOOLEAN NOT NULL DEFAULT TRUE,
            created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CHECK (dia_semana BETWEEN 1 AND 7),
            CHECK (horario_fim > horario_inicio)
        )'
    );
    $pdo->exec(
        'CREATE UNIQUE INDEX IF NOT EXISTS modalidade_horarios_slot_uk
         ON modalidade_horarios (modalidade_id, dia_semana, horario_inicio, horario_fim)
         WHERE ativo = TRUE'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS modalidade_horarios_public_idx
         ON modalidade_horarios (modalidade_id, dia_semana, horario_inicio)
         WHERE ativo = TRUE'
    );
};
