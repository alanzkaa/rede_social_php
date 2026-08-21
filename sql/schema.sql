-- Schema completo do banco de dados "db_rede_social".
-- Execute este arquivo do início ao fim para recriar o banco do zero.
-- A ordem das tabelas importa por causa das foreign keys (uma tabela
-- referenciada precisa existir antes da tabela que a referencia).

CREATE DATABASE IF NOT EXISTS db_rede_social CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_rede_social;

-- Usuários: base de todo o sistema.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome_usuario VARCHAR(50) UNIQUE,
    foto_perfil VARCHAR(255) NULL,
    data_nascimento DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Amizades: relação entre dois usuários, com status de pendente/aceita.
CREATE TABLE amizades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    amigo_id INT NOT NULL,
    status ENUM('pendente', 'aceita') NOT NULL DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (amigo_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    UNIQUE KEY (usuario_id, amigo_id)
);

-- Postagens: conteúdo publicado por um usuário.
CREATE TABLE postagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Curtidas: relação usuário <-> postagem.
CREATE TABLE curtidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    postagem_id INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (postagem_id) REFERENCES postagens(id) ON DELETE CASCADE,

    UNIQUE KEY (usuario_id, postagem_id)
);

-- Comentários: texto de um usuário vinculado a uma postagem específica.
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postagem_id INT NOT NULL,
    usuario_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (postagem_id) REFERENCES postagens(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);