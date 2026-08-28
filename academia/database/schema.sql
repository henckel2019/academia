-- ============================================
-- ACADEMIA GARAGEM AÇO - Schema MySQL
-- ============================================

CREATE DATABASE IF NOT EXISTS academia_garagem_aco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE academia_garagem_aco;

-- Perfis de acesso
CREATE TABLE perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO perfis (nome, descricao) VALUES
('SUPER_ADMIN', 'Acesso total'),
('ADMIN', 'Gestão geral'),
('FINANCE', 'Financeiro'),
('INSTRUCTOR', 'Professor/instrutor'),
('RECEPTION', 'Recepção'),
('STUDENT', 'Aluno');

-- Usuários administrativos / sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil_id INT NOT NULL,
    primeiro_acesso TINYINT(1) DEFAULT 1,
    ativo TINYINT(1) DEFAULT 1,
    ultimo_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Alunos / pessoas físicas
CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20) NULL,
    data_nascimento DATE NULL,
    sexo ENUM('M','F','O') DEFAULT 'O',
    foto VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    email VARCHAR(200) NULL,
    cep VARCHAR(10) NULL,
    logradouro VARCHAR(200) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    uf CHAR(2) NULL,
    contato_emergencia_nome VARCHAR(150) NULL,
    contato_emergencia_telefone VARCHAR(20) NULL,
    contato_emergencia_parentesco VARCHAR(50) NULL,
    matricula_codigo VARCHAR(50) UNIQUE,
    data_matricula DATE NULL,
    situacao ENUM('ATIVO','SUSPENSO','CANCELADO') DEFAULT 'ATIVO',
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Modalidades/lutas
CREATE TABLE modalidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Planos de mensalidade
CREATE TABLE planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    dia_vencimento INT NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Matrículas: aluno x modalidade x plano (permite múltiplas modalidades)
CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    modalidade_id INT NOT NULL,
    plano_id INT NOT NULL,
    valor_plano DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    situacao ENUM('ATIVA','SUSPENSA','CANCELADA') DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Graduações por modalidade
CREATE TABLE graduacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    modalidade_id INT NOT NULL,
    faixa VARCHAR(50) NOT NULL,
    grau INT DEFAULT 0,
    data_graduacao DATE NOT NULL,
    professor_responsavel VARCHAR(150) NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Professores / instrutores (podem ser usuários cadastrados)
CREATE TABLE professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(200) NULL,
    telefone VARCHAR(20) NULL,
    cref VARCHAR(50) NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Turmas
CREATE TABLE turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    modalidade_id INT NOT NULL,
    professor_id INT NULL,
    capacidade INT DEFAULT 0,
    dias_semana VARCHAR(50) NULL,
    horario_inicio TIME NULL,
    horario_fim TIME NULL,
    unidade VARCHAR(100) NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Alunos em turmas
CREATE TABLE turma_alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT NOT NULL,
    aluno_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_turma_aluno (turma_id, aluno_id)
) ENGINE=InnoDB;

-- Frequência
CREATE TABLE frequencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    turma_id INT NULL,
    modalidade_id INT NULL,
    data DATE NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_saida TIME NULL,
    origem ENUM('BIOMETRIA','MANUAL','APP') DEFAULT 'MANUAL',
    dispositivo VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Equipamentos biométricos
CREATE TABLE equipamentos_biometricos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    local VARCHAR(150) NOT NULL,
    modelo VARCHAR(100) NULL,
    serial VARCHAR(100) NULL,
    ip VARCHAR(45) NULL,
    api_token VARCHAR(255) NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Eventos de entrada biométrica (log)
CREATE TABLE eventos_entrada (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NULL,
    equipamento_id INT NOT NULL,
    capturado_em DATETIME NOT NULL,
    reconhecido TINYINT(1) DEFAULT 0,
    liberado TINYINT(1) DEFAULT 0,
    motivo_bloqueio VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos_biometricos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Mensalidades
CREATE TABLE mensalidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    competencia CHAR(7) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    valor_desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_final DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    status ENUM('ABERTA','VENCIDA','PAGA','CANCELADA') DEFAULT 'ABERTA',
    pix_qr_code TEXT NULL,
    pix_copia_e_cola TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_mensalidade_matricula_competencia (matricula_id, competencia)
) ENGINE=InnoDB;

-- Pagamentos (mock / integrações futuras)
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensalidade_id INT NOT NULL,
    tipo ENUM('PIX','BOLETO','CARTAO','DINHEIRO','OUTRO') DEFAULT 'OUTRO',
    valor_pago DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME NULL,
    comprovante TEXT NULL,
    confirmado TINYINT(1) DEFAULT 0,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mensalidade_id) REFERENCES mensalidades(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Promoções por antiguidade
CREATE TABLE promocoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('DESCONTO_PCT','DESCONTO_VALOR','PRECO_PROMOCIONAL','REGRA_TEMPO') DEFAULT 'DESCONTO_PCT',
    valor DECIMAL(10,2) DEFAULT 0.00,
    meses_inicio INT NOT NULL,
    meses_fim INT NULL,
    modalidade_id INT NULL,
    plano_id INT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Auditoria
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(150) NOT NULL,
    entidade VARCHAR(100) NOT NULL,
    entidade_id INT NULL,
    dados_antes TEXT NULL,
    dados_depois TEXT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Índices complementares
CREATE INDEX idx_alunos_situacao ON alunos(situacao);
CREATE INDEX idx_matriculas_aluno ON matriculas(aluno_id);
CREATE INDEX idx_mensalidades_status ON mensalidades(status);
CREATE INDEX idx_frequencias_data ON frequencias(data);
