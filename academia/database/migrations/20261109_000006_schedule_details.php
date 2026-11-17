<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec('ALTER TABLE modalidade_horarios ALTER COLUMN horario_fim DROP NOT NULL');
    $pdo->exec('ALTER TABLE modalidade_horarios ADD COLUMN IF NOT EXISTS observacao VARCHAR(150)');
};
