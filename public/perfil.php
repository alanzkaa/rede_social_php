<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';

// Só usuário logado pode ver o próprio perfil.
exigirLogin();

$usuario = buscarUsuarioPorId($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu perfil</title>
</head>
<body>

    <h1>Meu perfil</h1>

    <p><strong>Nome completo:</strong> <?= htmlspecialchars($usuario['nome_completo']) ?></p>

    <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>

    <p><strong>Nome de usuário:</strong>
        <?= $usuario['nome_usuario'] ? htmlspecialchars($usuario['nome_usuario']) : '(não definido)' ?>
    </p>

    <p><strong>Data de nascimento:</strong>
        <?= $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : '(não informada)' ?>
    </p>

    <p><strong>Membro desde:</strong>
        <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
    </p>

    <a href="perfil_editar.php">Editar perfil</a>
    |
    <a href="feed.php">Voltar ao feed</a>

</body>
</html>