ALTER TABLE usuarios
    ADD COLUMN ultima_atividade TIMESTAMP NULL DEFAULT NULL AFTER notif_mensagem;