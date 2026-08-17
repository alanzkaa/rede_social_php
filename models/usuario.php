<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cadastra um novo usuário no banco.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function cadastrarUsuario(string $nomeCompleto, string $email, string $senha, ?string $nomeUsuario, ?string $dataNascimento): bool|string
{
    $pdo = conectar();

    // Verifica se o e-mail já está cadastrado antes de tentar inserir.
    $sql = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->fetch()) {
        return 'Este e-mail já está cadastrado.';
    }

    // Gera o hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome_completo, email, senha, nome_usuario, data_nascimento)
            VALUES (:nome_completo, :email, :senha, :nome_usuario, :data_nascimento)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome_completo', $nomeCompleto);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':senha', $senhaHash);
    $stmt->bindValue(':nome_usuario', $nomeUsuario);
    $stmt->bindValue(':data_nascimento', $dataNascimento);

    return $stmt->execute();
}