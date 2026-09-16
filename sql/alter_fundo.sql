ALTER TABLE usuarios
    ADD COLUMN fundo ENUM('imagem', 'liso') NOT NULL DEFAULT 'imagem' AFTER tema;
