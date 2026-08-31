<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/postagem.php';
require_once __DIR__ . '/notificacao.php';

/**
 * Cria um comentário numa postagem.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function criarComentario(int $postagemId, int $usuarioId, string $conteudo): bool|string
{
    $conteudo = trim($conteudo);

    if ($conteudo === '') {
        return 'O comentário não pode estar vazio.';
    }

    $pdo = conectar();

    $sql = "INSERT INTO comentarios (postagem_id, usuario_id, conteudo)
            VALUES (:postagem_id, :usuario_id, :conteudo)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->bindValue(':conteudo', $conteudo);

    $sucesso = $stmt->execute();

    if ($sucesso) {
        // Notifica o dono do post (a função já ignora se ele comentou no próprio post).
        $donoId = buscarDonoDaPostagem($postagemId);
        if ($donoId !== null) {
            criarNotificacao($donoId, 'comentario', $usuarioId, $postagemId);
        }
    }

    return $sucesso;
}

/**
 * Lista os comentários de uma postagem, do mais antigo para o mais novo
 * (ordem de leitura natural de uma conversa).
 */
function listarComentarios(int $postagemId): array
{
    $pdo = conectar();

    $sql = "SELECT c.id, c.conteudo, c.data_criacao, u.id AS autor_id, u.nome_completo, u.nome_usuario, u.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.postagem_id = :postagem_id
            ORDER BY c.data_criacao ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':postagem_id', $postagemId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Exclui um comentário, mas somente se ele pertencer ao usuário informado.
 * Retorna true se de fato excluiu algo.
 */
function excluirComentario(int $comentarioId, int $usuarioId): bool
{
    $pdo = conectar();

    $sql = "DELETE FROM comentarios WHERE id = :id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $comentarioId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}