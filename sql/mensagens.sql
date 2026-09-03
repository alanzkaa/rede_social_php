CREATE TABLE conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1_id INT NOT NULL,
    usuario2_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario1_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    UNIQUE KEY (usuario1_id, usuario2_id)
);

CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    remetente_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Adiciona 'mensagem' como um tipo válido de notificação.
ALTER TABLE notificacoes
    MODIFY COLUMN tipo ENUM('curtida', 'comentario', 'solicitacao_amizade', 'amizade_aceita', 'mensagem') NOT NULL;

-- Preferência de notificação pra mensagens novas, ligada por padrão.
ALTER TABLE usuarios
    ADD COLUMN notif_mensagem TINYINT(1) NOT NULL DEFAULT 1 AFTER notif_amizade_aceita;