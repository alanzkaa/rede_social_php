<?php
session_start();

require_once __DIR__ . '/../includes/auth.php';

// Bloqueia acesso de quem não está logado.
exigirLogin();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Feed</title>
</head>
<body>

    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>

    <p>(Feed provisório)</p>

    <nav>
        <a href="perfil.php">Meu perfil</a>
        |
        <a href="amigos.php">Amigos</a>
        |
        <a href="logout.php">Sair</a>
    </nav>

</body>
</html>