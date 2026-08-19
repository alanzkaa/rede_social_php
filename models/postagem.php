<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cria uma nova postagem para o usuário informado.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function criarPostagem(int $usuarioId, string $conteudo): bool|string
{
    $conteudo = trim($conteudo);

    if ($conteudo === '') {
        return 'A postagem não pode estar vazia.';
    }

    $pdo = conectar();

    $sql = "INSERT INTO postagens (usuario_id, conteudo) VALUES (:usuario_id, :conteudo)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':conteudo', $conteudo);

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
    $sql = "SELECT p.id, p.conteudo, p.data_criacao, u.id AS autor_id, u.nome_completo, u.nome_usuario
            FROM postagens p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.usuario_id IN (
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