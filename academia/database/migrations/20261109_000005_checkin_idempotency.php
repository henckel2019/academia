<?php

declare(strict_types=1);
return static function (\PDO $pdo): void {
    // Keep historical records; enforce idempotency for new web check-ins after this migration.
    $pdo->exec("UPDATE frequencias SET origem='MANUAL' WHERE id IN (
        SELECT id FROM (SELECT id, ROW_NUMBER() OVER (PARTITION BY aluno_id, modalidade_id, data ORDER BY id) n
        FROM frequencias WHERE origem IN ('WEB','BIOMETRIA')) duplicates WHERE n > 1)");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS frequencias_checkin_dia_uk ON frequencias (aluno_id, modalidade_id, data) WHERE origem IN ('WEB','BIOMETRIA')");
};
