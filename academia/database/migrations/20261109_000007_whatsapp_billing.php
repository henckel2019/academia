<?php

declare(strict_types=1);
return static function (PDO $pdo): void {
    $pdo->exec('ALTER TABLE alunos ADD COLUMN IF NOT EXISTS whatsapp_cobranca BOOLEAN NOT NULL DEFAULT FALSE');
    $pdo->exec("CREATE TABLE IF NOT EXISTS whatsapp_cobrancas (
        id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        chave_envio UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
        mensalidade_id BIGINT NOT NULL UNIQUE REFERENCES mensalidades(id),
        status VARCHAR(12) NOT NULL DEFAULT 'PENDENTE' CHECK(status IN ('PENDENTE','ENVIADA','FALHA','REVISAO','CANCELADA')),
        telefone VARCHAR(20), mensagem TEXT, erro VARCHAR(255), mensagem_externa_id TEXT,
        enviada_em TIMESTAMPTZ, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("INSERT INTO configuracoes(chave,valor) VALUES
        ('whatsapp_cobranca_ativa','false'),('whatsapp_dias_antecedencia','0')
        ON CONFLICT(chave) DO NOTHING");
};
