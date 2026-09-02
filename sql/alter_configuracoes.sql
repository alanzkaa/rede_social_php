ALTER TABLE usuarios
    ADD COLUMN privacidade_postagens ENUM('publico', 'amigos') NOT NULL DEFAULT 'amigos' AFTER foto_perfil,
    ADD COLUMN aceita_solicitacoes TINYINT(1) NOT NULL DEFAULT 1 AFTER privacidade_postagens,
    ADD COLUMN notif_curtida TINYINT(1) NOT NULL DEFAULT 1 AFTER aceita_solicitacoes,
    ADD COLUMN notif_comentario TINYINT(1) NOT NULL DEFAULT 1 AFTER notif_curtida,
    ADD COLUMN notif_solicitacao_amizade TINYINT(1) NOT NULL DEFAULT 1 AFTER notif_comentario,
    ADD COLUMN notif_amizade_aceita TINYINT(1) NOT NULL DEFAULT 1 AFTER notif_solicitacao_amizade;