<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cria uma nova postagem para o usuário informado.
 * $visibilidade é 'amigos' (feed pessoal) ou 'comunidade' (feed público).
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function criarPostagem(int $usuarioId, string $conteudo, string $visibilidade = 'amigos'): bool|string
{
    $conteudo = trim($conteudo);

    if ($conteudo === '') {
        return 'A postagem não pode estar vazia.';
    }

    if ($visibilidade !== 'comunidade') {
        $visibilidade = 'amigos';
    }

    $pdo = conectar();

    $sql = "INSERT INTO postagens (usuario_id, conteudo, visibilidade) VALUES (:usuario_id, :conteudo, :visibilidade)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':conteudo', $conteudo);
    $stmt->bindValue(':visibilidade', $visibilidade);

    return $stmt->execute();
}

/**
 * Lista as postagens do usuário logado e de seus amigos confirmados,
 * das mais recentes para as mais antigas.
 */
function listarFeed(int $usuarioId): array
{
    $pdo = conectar();

    // A subquery pega os IDs de todos os amigos confirmados (nos dois sentidos),
    // e incluímos o próprio usuário na lista via UNION, pra ele ver os próprios posts também.
    $sql = "SELECT p.id, p.conteudo, p.data_criacao, u.id AS autor_id, u.nome_completo, u.nome_usuario, u.foto_perfil
            FROM postagens p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.visibilidade = 'amigos'
              AND p.usuario_id IN (
                SELECT amigo_id FROM amizades WHERE usuario_id = :usuario_id1 AND status = 'aceita'
                UNION
                SELECT usuario_id FROM amizades WHERE amigo_id = :usuario_id2 AND status = 'aceita'
                UNION
                SELECT :usuario_id3
            )
            ORDER BY p.data_criacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id1', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id2', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id3', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lista as postagens de um único usuário específico (para exibir no perfil dele),
 * das mais recentes para as mais antigas.
 */
function listarPostagensDoUsuario(int $usuarioId): array
{
    $pdo = conectar();

    $sql = "SELECT p.id, p.conteudo, p.data_criacao, p.visibilidade, u.id AS autor_id, u.nome_completo, u.nome_usuario, u.foto_perfil
            FROM postagens p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.usuario_id = :usuario_id
            ORDER BY p.data_criacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Exclui uma postagem, mas somente se ela pertencer ao usuário informado
 * (evita que alguém apague postagem de outra pessoa manipulando o formulário).
 * Retorna true se de fato excluiu algo.
 */
function excluirPostagem(int $postagemId, int $usuarioId): bool
{
    $pdo = conectar();

    $sql = "DELETE FROM postagens WHERE id = :id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $postagemId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

/**
 * Devolve o ID do dono de uma postagem, ou null se ela não existir.
 * Usada por curtida.php e comentario.php pra saber quem notificar.
 */
function buscarDonoDaPostagem(int $postagemId): ?int
{
    $pdo = conectar();

    $sql = "SELECT usuario_id FROM postagens WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $postagemId, PDO::PARAM_INT);
    $stmt->execute();

    $linha = $stmt->fetch();

    return $linha ? (int) $linha['usuario_id'] : null;
}

/**
 * Lista as postagens públicas da Comunidade (visibilidade 'comunidade'),
 * de qualquer usuário, das mais recentes para as mais antigas.
 */
function listarComunidade(int $limite = 30): array
{
    $pdo = conectar();

    $sql = "SELECT p.id, p.conteudo, p.data_criacao, u.id AS autor_id, u.nome_completo, u.nome_usuario, u.foto_perfil
            FROM postagens p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.visibilidade = 'comunidade'
            ORDER BY p.data_criacao DESC
            LIMIT :limite";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lista os posts públicos mais engajados (curtidas + comentários) dos
 * últimos 7 dias — a seção "Em alta" da Comunidade.
 */
function listarEmAlta(int $limite = 5): array
{
    $pdo = conectar();

    $sql = "SELECT p.id, p.conteudo, p.data_criacao, u.id AS autor_id, u.nome_completo, u.nome_usuario, u.foto_perfil,
                   (SELECT COUNT(*) FROM curtidas c WHERE c.postagem_id = p.id) AS total_curtidas,
                   (SELECT COUNT(*) FROM comentarios cm WHERE cm.postagem_id = p.id) AS total_comentarios
            FROM postagens p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.visibilidade = 'comunidade'
              AND p.data_criacao >= (NOW() - INTERVAL 7 DAY)
            ORDER BY (total_curtidas + total_comentarios) DESC, p.data_criacao DESC
            LIMIT :limite";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}