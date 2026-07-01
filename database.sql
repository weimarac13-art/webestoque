CREATE DATABASE IF NOT EXISTS webestoque;
USE webestoque;

CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    minQuantity INT NOT NULL DEFAULT 0,
    costPrice DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    salePrice DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    supplier VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    createdAt DATETIME NOT NULL,
    estoque VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS movements (
    id VARCHAR(50) PRIMARY KEY,
    productId VARCHAR(50) NOT NULL,
    productName VARCHAR(255) NOT NULL,
    type ENUM('ENTRADA', 'SAÍDA') NOT NULL,
    quantity INT NOT NULL,
    date DATETIME NOT NULL,
    reason VARCHAR(255) NOT NULL,
    responsible VARCHAR(255) NOT NULL,
    estoque VARCHAR(50),
    serie VARCHAR(255),
    tecnico VARCHAR(100),
    chamado VARCHAR(100),
    unidadeDestino VARCHAR(255),
    usuario VARCHAR(255),
    matricula VARCHAR(100),
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS technicians (
    id VARCHAR(50) PRIMARY KEY,
    matricula VARCHAR(100) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(20) NOT NULL,
    rg VARCHAR(20) NOT NULL,
    dataNascimento DATE NOT NULL,
    celPessoal VARCHAR(20) NOT NULL,
    celCorporativo VARCHAR(20) NOT NULL,
    createdAt DATETIME NOT NULL
);
  
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, perfil VARCHAR(50) NOT NULL);  
INSERT IGNORE INTO users (nome, username, password, perfil) VALUES ('Administrador', 'admin', '123', 'Administrador'); 

CREATE TABLE IF NOT EXISTS agencies (
    id VARCHAR(50) PRIMARY KEY,
    codigo VARCHAR(4) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    createdAt DATETIME NOT NULL
);
