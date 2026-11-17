<?php

declare(strict_types=1);

return static function (\PDO $pdo): void {
    $pdo->exec(
        "ALTER TABLE graduacoes
         ADD COLUMN IF NOT EXISTS valor NUMERIC(12,2) NOT NULL DEFAULT 0"
    );
    $pdo->exec(
        "DO $$
         BEGIN
             IF NOT EXISTS (
                 SELECT 1 FROM pg_constraint WHERE conname = 'graduacoes_valor_ck'
             ) THEN
                 ALTER TABLE graduacoes ADD CONSTRAINT graduacoes_valor_ck CHECK (valor >= 0);
             END IF;
         END $$"
    );
    $pdo->exec(
        "ALTER TABLE mensalidades
         ADD COLUMN IF NOT EXISTS tipo VARCHAR(20) NOT NULL DEFAULT 'MENSALIDADE',
         ADD COLUMN IF NOT EXISTS descricao VARCHAR(255),
         ADD COLUMN IF NOT EXISTS graduacao_id BIGINT"
    );
    $pdo->exec(
        "DO $$
         BEGIN
             IF NOT EXISTS (
                 SELECT 1 FROM pg_constraint WHERE conname = 'mensalidades_tipo_ck'
             ) THEN
                 ALTER TABLE mensalidades ADD CONSTRAINT mensalidades_tipo_ck CHECK (tipo IN ('MENSALIDADE', 'GRADUACAO', 'AVULSA'));
             END IF;
             IF NOT EXISTS (
                 SELECT 1 FROM pg_constraint WHERE conname = 'mensalidades_graduacao_fk'
             ) THEN
                 ALTER TABLE mensalidades ADD CONSTRAINT mensalidades_graduacao_fk
                     FOREIGN KEY (graduacao_id) REFERENCES graduacoes(id) ON DELETE SET NULL;
             END IF;
         END $$"
    );
    $pdo->exec('ALTER TABLE mensalidades DROP CONSTRAINT IF EXISTS mensalidades_matricula_id_competencia_key');
    $pdo->exec(
        "CREATE UNIQUE INDEX IF NOT EXISTS mensalidades_matricula_competencia_mensal_uk
         ON mensalidades (matricula_id, competencia)
         WHERE tipo = 'MENSALIDADE'"
    );
    $pdo->exec(
        "CREATE UNIQUE INDEX IF NOT EXISTS mensalidades_graduacao_uk
         ON mensalidades (graduacao_id)
         WHERE graduacao_id IS NOT NULL"
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS mensalidades_tipo_idx ON mensalidades (tipo, data_vencimento)'
    );
};
