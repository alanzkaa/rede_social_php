CREATE TABLE curtidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    postagem_id INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (postagem_id) REFERENCES postagens(id) ON DELETE CASCADE,

    UNIQUE KEY (usuario_id, postagem_id)
);