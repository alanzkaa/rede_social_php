<?php
session_start();

require_once __DIR__ . '/../models/postagem.php';
require_once __DIR__ . '/../models/curtida.php';
require_once __DIR__ . '/../models/comentario.php';
require_once __DIR__ . '/../models/amizade.php';
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'postar';

    if ($acao === 'curtir') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        alternarCurtida($usuarioId, $postagemId);
    } elseif ($acao === 'comentar') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        $conteudoComentario = $_POST['conteudo_comentario'] ?? '';
        criarComentario($postagemId, $usuarioId, $conteudoComentario);
    } elseif ($acao === 'excluir_post') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        excluirPostagem($postagemId, $usuarioId);
    } elseif ($acao === 'excluir_comentario') {
        $comentarioId = (int) ($_POST['comentario_id'] ?? 0);
        excluirComentario($comentarioId, $usuarioId);
    } else {
        $conteudo = $_POST['conteudo'] ?? '';
        $resultado = criarPostagem($usuarioId, $conteudo);

        if ($resultado !== true) {
            $erro = $resultado;
        }
    }
}

$posts = listarFeed($usuarioId);
$usuarioLogado = buscarUsuarioPorId($usuarioId);

// Lista de amigos para a coluna da direita — mostra só os primeiros,
// com link "Ver todos" apontando pra página completa de amigos.
$amigos = listarAmigos($usuarioId);
$amigosPreview = array_slice($amigos, 0, 6);
$paginaAtual = 'feed';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Início</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <?php if ($erro): ?>
                <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="POST" action="feed.php" class="card compose-box">
                <input type="hidden" name="acao" value="postar">
                <div class="compose-box__row">
                    <?= htmlFotoPerfil($usuarioLogado['foto_perfil'], 40, $usuarioLogado['nome_completo']) ?>
                    <textarea name="conteudo" placeholder="No que você está pensando?"></textarea>
                </div>
                <div class="compose-box__footer">
                    <button type="submit" class="btn btn--primary">Postar</button>
                </div>
            </form>

            <?php if (empty($posts)): ?>
                <div class="card empty-state">
                    Nenhuma postagem ainda. Adicione amigos ou faça sua primeira postagem!
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="card post">

                        <div class="post__header">
                            <?= htmlFotoPerfil($post['foto_perfil'], 36, $post['nome_completo']) ?>
                            <div>
                                <a href="perfil.php?id=<?= (int) $post['autor_id'] ?>">
                                    <?= htmlspecialchars($post['nome_completo']) ?>
                                    <?php if ($post['nome_usuario']): ?>
                                        (@<?= htmlspecialchars($post['nome_usuario']) ?>)
                                    <?php endif; ?>
                                </a>
                                <time><?= date('d/m/Y H:i', strtotime($post['data_criacao'])) ?></time>
                            </div>
                        </div>

                        <div class="post__body">
                            <p><?= nl2br(htmlspecialchars($post['conteudo'])) ?></p>
                        </div>

                        <div class="post__actions">
                            <?php $jaCurtiu = usuarioCurtiu($usuarioId, $post['id']); ?>
                            <form method="POST" action="feed.php">
                                <input type="hidden" name="acao" value="curtir">
                                <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="btn btn--success btn--small<?= $jaCurtiu ? ' is-active' : '' ?>">
                                    <?= $jaCurtiu ? 'Descurtir' : 'Curtir' ?>
                                </button>
                            </form>
                            <span class="post__likes"><?= contarCurtidas($post['id']) ?> curtida(s)</span>

                            <?php if ((int) $post['autor_id'] === $usuarioId): ?>
                                <form method="POST" action="feed.php" onsubmit="return confirm('Excluir esta postagem?');">
                                    <input type="hidden" name="acao" value="excluir_post">
                                    <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                                    <button type="submit" class="btn-link-danger">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="post__comments">
                            <?php foreach (listarComentarios($post['id']) as $comentario): ?>
                                <div class="comment">
                                    <?= htmlFotoPerfil($comentario['foto_perfil'], 28, $comentario['nome_completo']) ?>
                                    <div class="comment__body">
                                        <a href="perfil.php?id=<?= (int) $comentario['autor_id'] ?>">
                                            <?= htmlspecialchars($comentario['nome_completo']) ?>:
                                        </a>
                                        <?= htmlspecialchars($comentario['conteudo']) ?>

                                        <div class="comment__meta">
                                            <span><?= date('d/m/Y H:i', strtotime($comentario['data_criacao'])) ?></span>

                                            <?php if ((int) $comentario['autor_id'] === $usuarioId): ?>
                                                <form method="POST" action="feed.php" onsubmit="return confirm('Excluir este comentário?');">
                                                    <input type="hidden" name="acao" value="excluir_comentario">
                                                    <input type="hidden" name="comentario_id" value="<?= (int) $comentario['id'] ?>">
                                                    <button type="submit" class="btn-link-danger">Excluir</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <form method="POST" action="feed.php" class="comment-form">
                                <input type="hidden" name="acao" value="comentar">
                                <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                                <input type="text" name="conteudo_comentario" placeholder="Escreva um comentário...">
                                <button type="submit" class="btn btn--primary btn--small">Comentar</button>
                            </form>
                        </div>

                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

        </main>

        <aside class="sidebar sidebar--right">
            <h2>Amigos</h2>

            <?php if (empty($amigosPreview)): ?>
                <p style="font-size:0.85rem; color:var(--texto-suave);">Você ainda não tem amigos.</p>
            <?php else: ?>
                <ul class="friend-list">
                    <?php foreach ($amigosPreview as $amigo): ?>
                        <li>
                            <?= htmlFotoPerfil($amigo['foto_perfil'], 32, $amigo['nome_completo']) ?>
                            <a href="perfil.php?id=<?= (int) $amigo['id'] ?>"><?= htmlspecialchars($amigo['nome_completo']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="ver-todos" href="amigos.php">Ver todos →</a>
        </aside>

    </div>

    <script src="js/orb.js"></script>

</body>
</html>