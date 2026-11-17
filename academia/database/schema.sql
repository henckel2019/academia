-- Academia Garagem Aço — PostgreSQL 17+; aplicado por bin/migrate.php.

CREATE TABLE perfis (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO perfis (nome, descricao) VALUES
    ('SUPER_ADMIN', 'Acesso total e configurações críticas'), ('ADMIN', 'Gestão geral da academia'),
    ('FINANCE', 'Financeiro e relatórios'), ('INSTRUCTOR', 'Turmas, frequência e graduações'),
    ('RECEPTION', 'Cadastros, matrículas e check-in'), ('STUDENT', 'Acesso aos próprios dados')
ON CONFLICT (nome) DO NOTHING;

CREATE TABLE usuarios (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome VARCHAR(150) NOT NULL, email VARCHAR(200) NOT NULL, senha_hash VARCHAR(255) NOT NULL,
    perfil_id BIGINT NOT NULL REFERENCES perfis(id) ON DELETE RESTRICT,
    primeiro_acesso BOOLEAN NOT NULL DEFAULT TRUE, ativo BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_login TIMESTAMPTZ, senha_alterada_em TIMESTAMPTZ, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX usuarios_email_lower_uk ON usuarios (LOWER(email));

CREATE TABLE alunos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id BIGINT UNIQUE REFERENCES usuarios(id) ON DELETE SET NULL,
    nome VARCHAR(150) NOT NULL, cpf VARCHAR(14), rg VARCHAR(20), data_nascimento DATE,
    sexo CHAR(1) NOT NULL DEFAULT 'O' CHECK (sexo IN ('M', 'F', 'O')), foto VARCHAR(255),
    telefone VARCHAR(20), email VARCHAR(200), cep VARCHAR(10), logradouro VARCHAR(200), numero VARCHAR(20),
    complemento VARCHAR(100), bairro VARCHAR(100), cidade VARCHAR(100), uf CHAR(2),
    contato_emergencia_nome VARCHAR(150), contato_emergencia_telefone VARCHAR(20), contato_emergencia_parentesco VARCHAR(50),
    matricula_codigo VARCHAR(50), data_matricula DATE NOT NULL DEFAULT CURRENT_DATE,
    situacao VARCHAR(12) NOT NULL DEFAULT 'ATIVO' CHECK (situacao IN ('ATIVO','SUSPENSO','CANCELADO')),
    ativo BOOLEAN NOT NULL DEFAULT TRUE, observacoes TEXT, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX alunos_cpf_uk ON alunos (cpf) WHERE cpf IS NOT NULL;
CREATE UNIQUE INDEX alunos_matricula_codigo_uk ON alunos (matricula_codigo) WHERE matricula_codigo IS NOT NULL;
CREATE INDEX alunos_busca_idx ON alunos (nome, situacao) WHERE deleted_at IS NULL;

CREATE TABLE modalidades (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, nome VARCHAR(100) NOT NULL, descricao TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX modalidades_nome_ativa_uk ON modalidades (LOWER(nome)) WHERE deleted_at IS NULL;

CREATE TABLE modalidade_horarios (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    modalidade_id BIGINT NOT NULL REFERENCES modalidades(id) ON DELETE CASCADE,
    dia_semana SMALLINT NOT NULL CHECK (dia_semana BETWEEN 1 AND 7),
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (horario_fim > horario_inicio)
);
CREATE UNIQUE INDEX modalidade_horarios_slot_uk ON modalidade_horarios (modalidade_id, dia_semana, horario_inicio, horario_fim) WHERE ativo = TRUE;
CREATE INDEX modalidade_horarios_public_idx ON modalidade_horarios (modalidade_id, dia_semana, horario_inicio) WHERE ativo = TRUE;

CREATE TABLE planos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, nome VARCHAR(100) NOT NULL,
    valor NUMERIC(12,2) NOT NULL CHECK (valor >= 0), dia_vencimento SMALLINT NOT NULL CHECK (dia_vencimento BETWEEN 1 AND 31),
    descricao TEXT, ativo BOOLEAN NOT NULL DEFAULT TRUE, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX planos_nome_ativo_uk ON planos (LOWER(nome)) WHERE deleted_at IS NULL;

CREATE TABLE professores (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, usuario_id BIGINT UNIQUE REFERENCES usuarios(id) ON DELETE SET NULL,
    nome VARCHAR(150) NOT NULL, email VARCHAR(200), telefone VARCHAR(20), cref VARCHAR(50),
    ativo BOOLEAN NOT NULL DEFAULT TRUE, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX professores_cref_uk ON professores (cref) WHERE cref IS NOT NULL AND deleted_at IS NULL;

CREATE TABLE turmas (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, nome VARCHAR(100) NOT NULL,
    modalidade_id BIGINT NOT NULL REFERENCES modalidades(id) ON DELETE RESTRICT,
    professor_id BIGINT REFERENCES professores(id) ON DELETE SET NULL,
    capacidade INTEGER NOT NULL DEFAULT 0 CHECK (capacidade >= 0), dias_semana VARCHAR(50),
    horario_inicio TIME, horario_fim TIME, unidade VARCHAR(100), ativo BOOLEAN NOT NULL DEFAULT TRUE, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (horario_fim IS NULL OR horario_inicio IS NULL OR horario_fim > horario_inicio)
);

CREATE TABLE matriculas (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    aluno_id BIGINT NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT,
    modalidade_id BIGINT NOT NULL REFERENCES modalidades(id) ON DELETE RESTRICT,
    plano_id BIGINT NOT NULL REFERENCES planos(id) ON DELETE RESTRICT,
    valor_plano NUMERIC(12,2) NOT NULL CHECK (valor_plano >= 0), dia_vencimento SMALLINT NOT NULL DEFAULT 10, data_inicio DATE NOT NULL, data_fim DATE,
    situacao VARCHAR(12) NOT NULL DEFAULT 'ATIVA' CHECK (situacao IN ('ATIVA','SUSPENSA','CANCELADA')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (data_fim IS NULL OR data_fim >= data_inicio), CONSTRAINT matriculas_dia_vencimento_ck CHECK (dia_vencimento BETWEEN 1 AND 31)
);
CREATE UNIQUE INDEX matriculas_aluno_modalidade_aberta_uk ON matriculas (aluno_id, modalidade_id) WHERE situacao IN ('ATIVA','SUSPENSA');
CREATE INDEX matriculas_aluno_idx ON matriculas (aluno_id);

CREATE TABLE turma_alunos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, turma_id BIGINT NOT NULL REFERENCES turmas(id) ON DELETE RESTRICT,
    aluno_id BIGINT NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT, adicionado_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    removido_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL, removido_em TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX turma_alunos_ativo_uk ON turma_alunos (turma_id, aluno_id) WHERE removido_em IS NULL;

CREATE TABLE graduacoes (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, aluno_id BIGINT NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT,
    modalidade_id BIGINT NOT NULL REFERENCES modalidades(id) ON DELETE RESTRICT, faixa VARCHAR(50) NOT NULL,
    grau INTEGER NOT NULL DEFAULT 0 CHECK (grau >= 0), valor NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (valor >= 0), data_graduacao DATE NOT NULL,
    professor_id BIGINT REFERENCES professores(id) ON DELETE SET NULL, observacoes TEXT, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX graduacoes_aluno_modalidade_idx ON graduacoes (aluno_id, modalidade_id, data_graduacao DESC);

CREATE TABLE frequencias (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, aluno_id BIGINT NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT,
    turma_id BIGINT REFERENCES turmas(id) ON DELETE SET NULL, modalidade_id BIGINT REFERENCES modalidades(id) ON DELETE SET NULL,
    data DATE NOT NULL DEFAULT CURRENT_DATE, hora_entrada TIME NOT NULL, hora_saida TIME,
    origem VARCHAR(12) NOT NULL DEFAULT 'MANUAL' CHECK (origem IN ('MANUAL','WEB','BIOMETRIA','QR_CODE')),
    dispositivo VARCHAR(100), registrado_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, CHECK (hora_saida IS NULL OR hora_saida >= hora_entrada)
);
CREATE INDEX frequencias_aluno_data_idx ON frequencias (aluno_id, data DESC);

CREATE TABLE equipamentos_biometricos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, nome VARCHAR(100) NOT NULL, local VARCHAR(150) NOT NULL,
    modelo VARCHAR(100), serial VARCHAR(100), ip INET, api_token_hash CHAR(64), ativo BOOLEAN NOT NULL DEFAULT TRUE,
    status VARCHAR(12) NOT NULL DEFAULT 'UNKNOWN' CHECK (status IN ('ONLINE','OFFLINE','UNKNOWN','INATIVO')),
    ultimo_evento_em TIMESTAMPTZ, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX equipamentos_serial_uk ON equipamentos_biometricos (serial) WHERE serial IS NOT NULL;
CREATE UNIQUE INDEX equipamentos_token_uk ON equipamentos_biometricos (api_token_hash) WHERE api_token_hash IS NOT NULL;

CREATE TABLE eventos_entrada (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, evento_externo_id VARCHAR(120), aluno_id BIGINT REFERENCES alunos(id) ON DELETE SET NULL,
    equipamento_id BIGINT NOT NULL REFERENCES equipamentos_biometricos(id) ON DELETE RESTRICT, capturado_em TIMESTAMPTZ NOT NULL,
    reconhecido BOOLEAN NOT NULL DEFAULT FALSE, liberado BOOLEAN NOT NULL DEFAULT FALSE, motivo_bloqueio VARCHAR(255), payload JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX eventos_entrada_externo_uk ON eventos_entrada (evento_externo_id) WHERE evento_externo_id IS NOT NULL;

CREATE TABLE qr_tokens_aluno (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, aluno_id BIGINT NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT,
    token_hash CHAR(64) NOT NULL UNIQUE, gerado_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    expira_em TIMESTAMPTZ, revogado_em TIMESTAMPTZ, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX qr_tokens_aluno_ativo_uk ON qr_tokens_aluno (aluno_id) WHERE revogado_em IS NULL;

CREATE TABLE mensalidades (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, matricula_id BIGINT NOT NULL REFERENCES matriculas(id) ON DELETE RESTRICT,
    tipo VARCHAR(20) NOT NULL DEFAULT 'MENSALIDADE' CHECK (tipo IN ('MENSALIDADE','GRADUACAO','AVULSA')),
    descricao VARCHAR(255), graduacao_id BIGINT REFERENCES graduacoes(id) ON DELETE SET NULL,
    competencia VARCHAR(7) NOT NULL CHECK (competencia ~ '^[0-9]{4}-(0[1-9]|1[0-2])$'),
    valor NUMERIC(12,2) NOT NULL CHECK (valor >= 0), valor_desconto NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (valor_desconto >= 0),
    valor_final NUMERIC(12,2) NOT NULL CHECK (valor_final >= 0), data_vencimento DATE NOT NULL, data_pagamento DATE,
    status VARCHAR(12) NOT NULL DEFAULT 'ABERTA' CHECK (status IN ('ABERTA','VENCIDA','PAGA','CANCELADA')),
    pix_qr_code TEXT, pix_copia_e_cola TEXT, cancelada_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (valor_desconto <= valor), CHECK (status <> 'PAGA' OR data_pagamento IS NOT NULL)
);
CREATE UNIQUE INDEX mensalidades_matricula_competencia_mensal_uk ON mensalidades (matricula_id, competencia) WHERE tipo = 'MENSALIDADE';
CREATE UNIQUE INDEX mensalidades_graduacao_uk ON mensalidades (graduacao_id) WHERE graduacao_id IS NOT NULL;
CREATE INDEX mensalidades_status_vencimento_idx ON mensalidades (status, data_vencimento);
CREATE INDEX mensalidades_tipo_idx ON mensalidades (tipo, data_vencimento);

CREATE TABLE pagamentos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, mensalidade_id BIGINT NOT NULL REFERENCES mensalidades(id) ON DELETE RESTRICT,
    tipo VARCHAR(12) NOT NULL CHECK (tipo IN ('PIX','BOLETO','CARTAO','DINHEIRO','OUTRO')),
    valor_pago NUMERIC(12,2) NOT NULL CHECK (valor_pago > 0), data_pagamento TIMESTAMPTZ, comprovante TEXT,
    confirmado BOOLEAN NOT NULL DEFAULT FALSE, confirmado_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    confirmado_em TIMESTAMPTZ, referencia_externa VARCHAR(150), observacoes TEXT, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (NOT confirmado OR (data_pagamento IS NOT NULL AND confirmado_em IS NOT NULL))
);
CREATE UNIQUE INDEX pagamentos_mensalidade_confirmado_uk ON pagamentos (mensalidade_id) WHERE confirmado;
CREATE UNIQUE INDEX pagamentos_referencia_externa_uk ON pagamentos (referencia_externa) WHERE referencia_externa IS NOT NULL;

CREATE TABLE promocoes (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('DESCONTO_PCT','DESCONTO_VALOR','PRECO_PROMOCIONAL','REGRA_TEMPO')),
    valor NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (valor >= 0), meses_inicio INTEGER NOT NULL CHECK (meses_inicio >= 0),
    meses_fim INTEGER CHECK (meses_fim IS NULL OR meses_fim >= meses_inicio), modalidade_id BIGINT REFERENCES modalidades(id) ON DELETE SET NULL,
    plano_id BIGINT REFERENCES planos(id) ON DELETE SET NULL, ativo BOOLEAN NOT NULL DEFAULT TRUE, deleted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE configuracoes (
    chave VARCHAR(100) PRIMARY KEY, valor JSONB NOT NULL, atualizado_por_usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO configuracoes (chave, valor) VALUES
    ('nome_academia','"Academia Garagem Aço"'::jsonb), ('cnpj','""'::jsonb), ('telefone','""'::jsonb), ('email','""'::jsonb),
    ('endereco','""'::jsonb), ('logo','""'::jsonb), ('dias_tolerancia_financeira','5'::jsonb), ('dia_padrao_vencimento','10'::jsonb),
    ('moeda','"BRL"'::jsonb), ('timezone','"America/Sao_Paulo"'::jsonb), ('permitir_bloqueio_inadimplencia','true'::jsonb),
    ('tempo_expiracao_sessao','7200'::jsonb), ('max_tentativas_login','5'::jsonb)
ON CONFLICT (chave) DO NOTHING;

CREATE TABLE login_attempts (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, email_hash CHAR(64) NOT NULL, ip_hash CHAR(64) NOT NULL,
    sucesso BOOLEAN NOT NULL DEFAULT FALSE, attempted_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX login_attempts_limiter_idx ON login_attempts (email_hash, ip_hash, attempted_at DESC) WHERE NOT sucesso;

CREATE TABLE auditoria (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    acao VARCHAR(150) NOT NULL, entidade VARCHAR(100) NOT NULL, entidade_id BIGINT,
    dados_antes JSONB, dados_depois JSONB, ip INET, user_agent VARCHAR(255), created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX auditoria_entidade_idx ON auditoria (entidade, entidade_id, created_at DESC);

CREATE TABLE financeiro_historico (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, mensalidade_id BIGINT NOT NULL REFERENCES mensalidades(id) ON DELETE RESTRICT,
    usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL, acao VARCHAR(100) NOT NULL,
    dados_antes JSONB, dados_depois JSONB, created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX financeiro_historico_mensalidade_idx ON financeiro_historico (mensalidade_id, created_at DESC);
