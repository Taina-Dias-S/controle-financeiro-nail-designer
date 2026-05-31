CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome_negocio VARCHAR(150) DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    origem ENUM('negocio', 'pessoal', 'ambos') NOT NULL DEFAULT 'ambos',
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE formas_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE lancamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    id_forma_pagamento INT DEFAULT NULL,
    data_lancamento DATE NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    origem ENUM('negocio', 'pessoal') NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    valor DECIMAL(10,2) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id),
    FOREIGN KEY (id_forma_pagamento) REFERENCES formas_pagamento(id)
);

INSERT INTO categorias (nome, tipo, origem) VALUES
('Atendimento', 'entrada', 'negocio'),
('Pacote', 'entrada', 'negocio'),
('Curso', 'entrada', 'negocio'),
('Extra', 'entrada', 'negocio'),
('Materiais', 'saida', 'negocio'),
('Manutenção', 'saida', 'negocio'),
('Descartáveis', 'saida', 'negocio'),
('Taxas', 'saida', 'negocio'),
('Divulgação', 'saida', 'negocio'),
('Outros', 'saida', 'negocio'),
('Alimentação', 'saida', 'pessoal'),
('Transporte', 'saida', 'pessoal'),
('Contas', 'saida', 'pessoal'),
('Lazer', 'saida', 'pessoal'),
('Outros', 'saida', 'pessoal');

INSERT INTO formas_pagamento (nome) VALUES
('Pix'),
('Dinheiro'),
('Cartão de crédito'),
('Cartão de débito'),
('Outro');
