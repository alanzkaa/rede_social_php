<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Alterna a curtida de um usuário numa postagem: se já curtiu, remove a curtida;
 * se ainda não curtiu, adiciona. Retorna o novo estado: true (curtiu) ou false (descurtiu).
 */
function alternarCurtida(int $usuarioId, int $postagemId): bool
{
    $pdo = conectar();

    if (usuarioCurtiu($usuarioId, $postagemId)) {
        $sql = "DELETE FROM curtidas WHERE usuario_id = :usuario_id AND postagem_id = :postagem_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
        $stmt->execute();

        return false;
    }

    $sql = "INSERT INTO curtidas (usuario_id, postagem_id) VALUES (:usuario_id, :postagem_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
    $stmt->execute();

    return true;
}

/**
 * Confere se um usuário já curtiu uma postagem específica.
 */
function usuarioCurtiu(int $usuarioId, int $postagemId): bool
{
    $pdo = conectar();

    $sql = "SELECT id FROM curtidas WHERE usuario_id = :usuario_id AND postagem_id = :postagem_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
    $stmt->execute();

    return (bool) $stmt->fetch();
}

/**
 * Conta quantas curtidas uma postagem tem.
 */
function contarCurtidas(int $postagemId): int
{
    $pdo = conectar();

    $sql = "SELECT COUNT(*) AS total FROM curtidas WHERE postagem_id = :postagem_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $stmt->fetch()['total'];
}