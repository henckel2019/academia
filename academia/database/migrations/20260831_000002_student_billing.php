<?php

declare(strict_types=1);

return static function (\PDO $pdo): void {
    $pdo->exec('ALTER TABLE matriculas ADD COLUMN IF NOT EXISTS dia_vencimento SMALLINT');
    $pdo->exec(
        'UPDATE matriculas AS m
         SET dia_vencimento = COALESCE(p.dia_vencimento, 10)
         FROM planos AS p
         WHERE p.id = m.plano_id AND m.dia_vencimento IS NULL'
    );
    $pdo->exec('UPDATE matriculas SET dia_vencimento = 10 WHERE dia_vencimento IS NULL');
    $pdo->exec('ALTER TABLE matriculas ALTER COLUMN dia_vencimento SET DEFAULT 10');
    $pdo->exec('ALTER TABLE matriculas ALTER COLUMN dia_vencimento SET NOT NULL');
    $pdo->exec(
        "DO $$
        BEGIN
            IF NOT EXISTS (
                SELECT 1 FROM pg_constraint WHERE conname = 'matriculas_dia_vencimento_ck'
            ) THEN
                ALTER TABLE matriculas ADD CONSTRAINT matriculas_dia_vencimento_ck CHECK (dia_vencimento BETWEEN 1 AND 31);
            END IF;
        END
        $$"
    );
};
