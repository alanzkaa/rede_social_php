<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/amizade.php';
require_once __DIR__ . '/notificacao.php';

/**
 * Busca a conversa entre dois usuários, sem criar se não existir.
 * Retorna o ID da conversa, ou null se ainda não existe nenhuma mensagem trocada.
 */
function buscarConversaEntre(int $usuarioA, int $usuarioB): ?int
{
    $menor = min($usuarioA, $usuarioB);
    $maior = max($usuarioA, $usuarioB);

    $pdo = conectar();

    $sql = "SELECT id FROM conversas WHERE usuario1_id = :menor AND usuario2_id = :maior";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':menor', $menor, PDO::PARAM_INT);
    $stmt->bindValue(':maior', $maior, PDO::PARAM_INT);
    $stmt->execute();

    $linha = $stmt->fetch();

    return $linha ? (int) $linha['id'] : null;
}

/**
 * Busca a conversa entre dois usuários, criando uma nova se ainda não existir.
 * Usa "ordem canônica" (sempre o menor ID em usuario1_id) pra garantir que
 * nunca existam duas linhas pra mesma dupla de pessoas.
 */
function buscarOuCriarConversa(int $usuarioA, int $usuarioB): int
{
    $conversaId = buscarConversaEntre($usuarioA, $usuarioB);

    if ($conversaId !== null) {
        return $conversaId;
    }

    $menor = min($usuarioA, $usuarioB);
    $maior = max($usuarioA, $usuarioB);

    $pdo = conectar();

    $sql = "INSERT INTO conversas (usuario1_id, usuario2_id) VALUES (:menor, :maior)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':menor', $menor, PDO::PARAM_INT);
    $stmt->bindValue(':maior', $maior, PDO::PARAM_INT);
    $stmt->execute();

    // lastInsertId() devolve o ID que acabou de ser gerado pelo AUTO_INCREMENT.
    return (int) $pdo->lastInsertId();
}

/**
 * Envia uma mensagem de $remetenteId para $destinatarioId.
 * Só permite entre amigos. Retorna true em caso de sucesso, ou uma string de erro.
 */
function enviarMensagem(int $remetenteId, int $destinatarioId, string $conteudo): bool|string
{
    $conteudo = trim($conteudo);

    if ($conteudo === '') {
        return 'A mensagem não pode estar vazia.';
    }

    if ($remetenteId === $destinatarioId) {
        return 'Você não pode enviar mensagem para si mesmo.';
    }

    $status = verificarStatusAmizade($remetenteId, $destinatarioId)['status'];
    if ($status !== 'amigos') {
        return 'Vocês precisam ser amigos para trocar mensagens.';
    }

    $conversaId = buscarOuCriarConversa($remetenteId, $destinatarioId);

    $pdo = conectar();

    $sql = "INSERT INTO mensagens (conversa_id, remetente_id, conteudo) VALUES (:conversa_id, :remetente_id, :conteudo)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':conversa_id', $conversaId, PDO::PARAM_INT);
    $stmt->bindValue(':remetente_id', $remetenteId, PDO::PARAM_INT);
    $stmt->bindValue(':conteudo', $conteudo);

    $sucesso = $stmt->execute();

    if ($sucesso) {
        criarNotificacao($destinatarioId, 'mensagem', $remetenteId);
    }

    return $sucesso;
}

/**
 * Lista as mensagens de uma conversa, da mais antiga para a mais nova
 * (ordem de leitura natural de um chat).
 */
function listarMensagens(int $conversaId): array
{
    $pdo = conectar();

    $sql = "SELECT m.id, m.remetente_id, m.conteudo, m.lida, m.data_envio, u.nome_completo, u.foto_perfil
            FROM mensagens m
            JOIN usuarios u ON u.id = m.remetente_id
            WHERE m.conversa_id = :conversa_id
            ORDER BY m.data_envio ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':conversa_id', $conversaId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Marca como lidas as mensagens de uma conversa que NÃO foram enviadas
 * pelo usuário informado (ou seja, marca como lidas as mensagens que ele recebeu).
 */
function marcarConversaComoLida(int $conversaId, int $usuarioId): void
{
    $pdo = conectar();

    $sql = "UPDATE mensagens
            SET lida = 1
            WHERE conversa_id = :conversa_id AND remetente_id != :usuario_id AND lida = 0";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':conversa_id', $conversaId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Lista as conversas de um usuário, com os dados da outra pessoa, a última
 * mensagem trocada e a contagem de não lidas — pra montar a caixa de entrada.
 */
function listarConversas(int $usuarioId): array
{
    $pdo = conectar();

    $sql = "SELECT c.id AS conversa_id,
                   u.id AS outro_id, u.nome_completo, u.nome_usuario, u.foto_perfil, u.ultima_atividade,
                   (SELECT conteudo FROM mensagens m WHERE m.conversa_id = c.id ORDER BY m.data_envio DESC LIMIT 1) AS ultima_mensagem,
                   (SELECT data_envio FROM mensagens m WHERE m.conversa_id = c.id ORDER BY m.data_envio DESC LIMIT 1) AS ultima_data,
                   (SELECT COUNT(*) FROM mensagens m WHERE m.conversa_id = c.id AND m.remetente_id != :usuario_id1 AND m.lida = 0) AS nao_lidas
            FROM conversas c
            JOIN usuarios u ON u.id = IF(c.usuario1_id = :usuario_id2, c.usuario2_id, c.usuario1_id)
            WHERE c.usuario1_id = :usuario_id3 OR c.usuario2_id = :usuario_id4
            ORDER BY ultima_data DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id1', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id2', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id3', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id4', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Conta o total de mensagens não lidas de um usuário, somando todas as
 * conversas — pro contador de mensagens na navbar.
 */
function contarMensagensNaoLidas(int $usuarioId): int
{
    $pdo = conectar();

    $sql = "SELECT COUNT(*) AS total
            FROM mensagens m
            JOIN conversas c ON c.id = m.conversa_id
            WHERE (c.usuario1_id = :usuario_id1 OR c.usuario2_id = :usuario_id2)
              AND m.remetente_id != :usuario_id3
              AND m.lida = 0";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id1', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id2', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id3', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $stmt->fetch()['total'];
}