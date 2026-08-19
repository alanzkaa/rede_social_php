<?php
session_start();

require_once __DIR__ . '/../models/postagem.php';
require_once __DIR__ . '/../includes/auth.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conteudo = $_POST['conteudo'] ?? '';
    $resultado = criarPostagem($usuarioId, $conteudo);

    if ($resultado !== true) {
        $erro = $resultado;
    }
    // Se deu certo, não precisamos fazer nada além de deixar o código
    // continuar e recarregar o feed já com o novo post listado.
}

$posts = listarFeed($usuarioId);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Feed</title>
</head>
<body>

    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>

    <nav>
        <a href="perfil.php">Meu perfil</a>
        |
        <a href="amigos.php">Amigos</a>
        |
        <a href="logout.php">Sair</a>
    </nav>

    <hr>

    <h2>Nova postagem</h2>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST" action="feed.php">
        <textarea name="conteudo" rows="3" cols="50" placeholder="No que você está pensando?"></textarea><br>
        <button type="submit">Postar</button>
    </form>

    <hr>

    <h2>Feed</h2>

    <?php if (empty($posts)): ?>
        <p>Nenhuma postagem ainda. Adicione amigos ou faça sua primeira postagem!</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <strong><?= htmlspecialchars($post['nome_completo']) ?></strong>
                <?php if ($post['nome_usuario']): ?>
                    (@<?= htmlspecialchars($post['nome_usuario']) ?>)
                <?php endif; ?>
                <br>
                <small><?= date('d/m/Y H:i', strtotime($post['data_criacao'])) ?></small>
                <p><?= nl2br(htmlspecialchars($post['conteudo'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>