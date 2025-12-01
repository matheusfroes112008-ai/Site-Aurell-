-- ==============================
-- BANCO DE DADOS AURELLÉ (ATUALIZADO)
-- ==============================

CREATE DATABASE IF NOT EXISTS aurelle;
USE aurelle;

-- ==============================
-- TABELA DE USUÁRIOS
-- ==============================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    data_nascimento DATE,
    endereco VARCHAR(200),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10)
);

-- ==============================
-- TABELA DE CATEGORIAS
-- ==============================
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- ==============================
-- TABELA DE PRODUTOS
-- ==============================
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255),
    id_categoria INT,
    estoque INT DEFAULT 0,
    destaque BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

-- ==============================
-- TABELA DE CARRINHO
-- ==============================
CREATE TABLE IF NOT EXISTS carrinho (
    id_carrinho INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_produto INT,
    quantidade INT DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

-- ==============================
-- TABELA DE PEDIDOS
-- ==============================
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2),
    status ENUM('Pendente', 'Pago', 'Enviado', 'Entregue') DEFAULT 'Pendente',
    metodo_pagamento VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ==============================
-- TABELA DE ITENS DO PEDIDO
-- ==============================
CREATE TABLE IF NOT EXISTS itens_pedido (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_produto INT,
    quantidade INT,
    preco_unitario DECIMAL(10,2),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

-- ==============================
-- TABELA DE ADMINISTRADOR
-- ==============================
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- ==============================
-- TABELA DE MENSAGENS DE CONTATO
-- ==============================
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categorias (nome) VALUES
('Anéis'), ('Colares'), ('Pulseiras'), ('Brincos'),
('Relógios'), ('Pérolas'), ('Ouro'), ('Diamante');

INSERT INTO admin (usuario, senha) VALUES
('admin', '$2y$10$Vh4vCztgLqUeE4K5ZKZHeuB9f5mFjSxHCC5vmt4vOozap19f5jv5y');
