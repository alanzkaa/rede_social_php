ALTER TABLE postagens
    ADD COLUMN visibilidade ENUM('amigos', 'comunidade') NOT NULL DEFAULT 'amigos' AFTER usuario_id;