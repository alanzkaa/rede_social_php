<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Envia uma solicitação de amizade de $usuarioId para $amigoId.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function enviarSolicitacao(int $usuarioId, int $amigoId): bool|string
{
    if ($usuarioId === $amigoId) {
        return 'Você não pode adicionar a si mesmo.';
    }

    $pdo = conectar();

    // Confere se já existe alguma relação entre os dois, em qualquer sentido.
    $sql = "SELECT id, usuario_id, status FROM amizades
            WHERE (usuario_id = :usuario_id AND amigo_id = :amigo_id)
               OR (usuario_id = :amigo_id2 AND amigo_id = :usuario_id2)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':amigo_id', $amigoId, PDO::PARAM_INT);
    $stmt->bindValue(':amigo_id2', $amigoId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id2', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    $existente = $stmt->fetch();

    if ($existente) {
        if ($existente['status'] === 'aceita') {
            return 'Vocês já são amigos.';
        }
        return 'Já existe uma solicitação pendente entre vocês.';
    }

    $sql = "INSERT INTO amizades (usuario_id, amigo_id, status) VALUES (:usuario_id, :amigo_id, 'pendente')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':amigo_id', $amigoId, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Aceita uma solicitação de amizade pendente.
 * $usuarioLogadoId precisa ser o amigo_id da solicitação (quem recebeu o pedido),
 * por segurança — evita que alguém aceite uma solicitação que não é dele.
 */
function aceitarSolicitacao(int $solicitacaoId, int $usuarioLogadoId): bool
{
    $pdo = conectar();

    $sql = "UPDATE amizades
            SET status = 'aceita'
            WHERE id = :id AND amigo_id = :usuario_logado_id AND status = 'pendente'";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $solicitacaoId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_logado_id', $usuarioLogadoId, PDO::PARAM_INT);
    $stmt->execute();

    // rowCount() diz quantas linhas foram alteradas. Se for 0, não achou
    // uma solicitação pendente pra esse usuário com esse ID.
    return $stmt->rowCount() > 0;
}

/**
 * Recusa (apaga) uma solicitação de amizade pendente.
 */
function recusarSolicitacao(int $solicitacaoId, int $usuarioLogadoId): bool
{
    $pdo = conectar();

    $sql = "DELETE FROM amizades
            WHERE id = :id AND amigo_id = :usuario_logado_id AND status = 'pendente'";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $solicitacaoId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_logado_id', $usuarioLogadoId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

/**
 * Lista os amigos já confirmados (status 'aceita') de um usuário,
 * em qualquer sentido da relação (ele pode ter enviado ou recebido o pedido).
 */
function listarAmigos(int $usuarioId): array
{
    $pdo = conectar();

    $sql = "SELECT u.id, u.nome_completo, u.nome_usuario
            FROM amizades a
            JOIN usuarios u ON u.id = IF(a.usuario_id = :usuario_id, a.amigo_id, a.usuario_id)
            WHERE (a.usuario_id = :usuario_id2 OR a.amigo_id = :usuario_id3)
              AND a.status = 'aceita'
            ORDER BY u.nome_completo";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id2', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id3', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lista as solicitações pendentes que o usuário RECEBEU (aguardando ele aceitar/recusar).
 */
function listarSolicitacoesPendentes(int $usuarioId): array
{
    $pdo = conectar();

    $sql = "SELECT a.id AS solicitacao_id, u.id AS usuario_id, u.nome_completo, u.nome_usuario
            FROM amizades a
            JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.amigo_id = :usuario_id AND a.status = 'pendente'
            ORDER BY a.data_solicitacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}