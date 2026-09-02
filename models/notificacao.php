<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cria uma notificação para $usuarioId, causada pela ação de $atorId.
 * Nunca cria notificação de alguém para si mesmo (ex: curtir o próprio post).
 */
function criarNotificacao(int $usuarioId, string $tipo, int $atorId, ?int $postagemId = null): void
{
    if ($usuarioId === $atorId) {
        return;
    }

    // Cada tipo de notificação tem uma coluna de preferência correspondente
    // na tabela usuarios. Só os tipos aqui listados são checados; qualquer
    // tipo novo que não estiver no mapa é sempre criado.
    $colunasPreferencia = [
        'curtida'              => 'notif_curtida',
        'comentario'           => 'notif_comentario',
        'solicitacao_amizade'  => 'notif_solicitacao_amizade',
        'amizade_aceita'       => 'notif_amizade_aceita',
    ];

    $pdo = conectar();

    if (isset($colunasPreferencia[$tipo])) {
        $coluna = $colunasPreferencia[$tipo];

        $sql = "SELECT {$coluna} AS preferencia FROM usuarios WHERE id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();

        // Se o usuário desligou esse tipo de notificação, não cria nada.
        if ($linha && (int) $linha['preferencia'] === 0) {
            return;
        }
    }

    $sql = "INSERT INTO notificacoes (usuario_id, tipo, ator_id, postagem_id)
            VALUES (:usuario_id, :tipo, :ator_id, :postagem_id)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':tipo', $tipo);
    $stmt->bindValue(':ator_id', $atorId, PDO::PARAM_INT);
    $stmt->bindValue(':postagem_id', $postagemId, $postagemId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Lista as notificações mais recentes de um usuário, com os dados de quem
 * causou cada uma (nome, foto).
 */
function listarNotificacoes(int $usuarioId, int $limite = 8): array
{
    $pdo = conectar();

    $sql = "SELECT n.id, n.tipo, n.postagem_id, n.lida, n.data_criacao,
                   u.id AS ator_id, u.nome_completo AS ator_nome, u.foto_perfil AS ator_foto
            FROM notificacoes n
            JOIN usuarios u ON u.id = n.ator_id
            WHERE n.usuario_id = :usuario_id
            ORDER BY n.data_criacao DESC
            LIMIT :limite";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Conta quantas notificações não lidas um usuário tem (pro contador do sino).
 */
function contarNaoLidas(int $usuarioId): int
{
    $pdo = conectar();

    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE usuario_id = :usuario_id AND lida = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $stmt->fetch()['total'];
}

/**
 * Marca todas as notificações de um usuário como lidas.
 */
function marcarTodasComoLidas(int $usuarioId): void
{
    $pdo = conectar();

    $sql = "UPDATE notificacoes SET lida = 1 WHERE usuario_id = :usuario_id AND lida = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
}