<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/amizade.php';
require_once __DIR__ . '/../models/postagem.php';
require_once __DIR__ . '/../models/curtida.php';
require_once __DIR__ . '/../models/comentario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioLogadoId = $_SESSION['usuario_id'];

// Se vier ?id= na URL, mostramos o perfil de outra pessoa; senão, o próprio.
$idVisualizado = isset($_GET['id']) ? (int) $_GET['id'] : $usuarioLogadoId;
$ehProprioPerfil = ($idVisualizado === $usuarioLogadoId);

$usuario = buscarUsuarioPorId($idVisualizado);

// Se o ID na URL não existe no banco, não tem o que mostrar.
if (!$usuario) {
    header('Location: feed.php');
    exit;
}

$mensagem = null;

// Ações vindas de formulários (amizade, curtir, comentar, excluir).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar' && !$ehProprioPerfil) {
        $resultado = enviarSolicitacao($usuarioLogadoId, $idVisualizado);
        $mensagem = $resultado === true ? 'Solicitação enviada!' : $resultado;
    } elseif ($acao === 'aceitar' && !$ehProprioPerfil) {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        aceitarSolicitacao($solicitacaoId, $usuarioLogadoId);
    } elseif ($acao === 'recusar' && !$ehProprioPerfil) {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        recusarSolicitacao($solicitacaoId, $usuarioLogadoId);
    } elseif ($acao === 'curtir') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        $statusAtual = $ehProprioPerfil ? 'amigos' : verificarStatusAmizade($usuarioLogadoId, $idVisualizado)['status'];
        if ($ehProprioPerfil || $statusAtual === 'amigos') {
            alternarCurtida($usuarioLogadoId, $postagemId);
        }
    } elseif ($acao === 'comentar') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        $statusAtual = $ehProprioPerfil ? 'amigos' : verificarStatusAmizade($usuarioLogadoId, $idVisualizado)['status'];
        if ($ehProprioPerfil || $statusAtual === 'amigos') {
            $conteudoComentario = $_POST['conteudo_comentario'] ?? '';
            criarComentario($postagemId, $usuarioLogadoId, $conteudoComentario);
        }
    } elseif ($acao === 'desfazer_amizade' && !$ehProprioPerfil) {
        $ok = desfazerAmizade($usuarioLogadoId, $idVisualizado);
        $mensagem = $ok ? 'Amizade desfeita.' : 'Não foi possível desfazer a amizade.';
    } elseif ($acao === 'excluir_post') {
        $postagemId = (int) ($_POST['postagem_id'] ?? 0);
        excluirPostagem($postagemId, $usuarioLogadoId);
    } elseif ($acao === 'excluir_comentario') {
        $comentarioId = (int) ($_POST['comentario_id'] ?? 0);
        excluirComentario($comentarioId, $usuarioLogadoId);
    }
}

$statusAmizade = $ehProprioPerfil ? null : verificarStatusAmizade($usuarioLogadoId, $idVisualizado);

// Só mostra as postagens se for o próprio perfil, ou se os dois forem amigos.
$podeVerPostagens = $ehProprioPerfil || ($statusAmizade['status'] === 'amigos');
$posts = $podeVerPostagens ? listarPostagensDoUsuario($idVisualizado) : [];

// Dados do usuário logado, pra montar a navbar/sidebar (que sempre mostram
// o PRÓPRIO usuário, mesmo quando a página exibe o perfil de outra pessoa).
$usuarioLogado = $ehProprioPerfil ? $usuario : buscarUsuarioPorId($usuarioLogadoId);
$paginaAtual = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · <?= $ehProprioPerfil ? 'Meu perfil' : htmlspecialchars($usuario['nome_completo']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <?php if ($mensagem): ?>
                <p class="alert-info"><?= htmlspecialchars($mensagem) ?></p>
            <?php endif; ?>

            <section class="card card--padded profile-header">
                <?= htmlFotoPerfil($usuario['foto_perfil'], 96, $usuario['nome_completo']) ?>

                <div class="profile-header__info">
                    <h1><?= $ehProprioPerfil ? 'Meu perfil' : htmlspecialchars($usuario['nome_completo']) ?></h1>

                    <div class="profile-header__meta">
                        <?php if ($ehProprioPerfil): ?>
                            <span><?= htmlspecialchars($usuario['email']) ?></span>
                        <?php endif; ?>
                        <span>
                            <?= $usuario['nome_usuario'] ? '@' . htmlspecialchars($usuario['nome_usuario']) : 'sem nome de usuário' ?>
                        </span>
                        <span>
                            <?= $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : 'data de nascimento não informada' ?>
                        </span>
                        <span>Membro desde <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?></span>
                    </div>

                    <div class="profile-header__actions">
                        <?php if ($ehProprioPerfil): ?>
                            <a href="perfil_editar.php" class="btn btn--primary btn--small">Editar perfil</a>
                        <?php elseif ($statusAmizade['status'] === 'nenhuma'): ?>
                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                                <input type="hidden" name="acao" value="enviar">
                                <button type="submit" class="btn btn--primary btn--small">Adicionar amigo</button>
                            </form>
                        <?php elseif ($statusAmizade['status'] === 'pendente_enviada'): ?>
                            <span class="texto-suave">Solicitação enviada, aguardando resposta.</span>
                        <?php elseif ($statusAmizade['status'] === 'pendente_recebida'): ?>
                            <span class="texto-suave">Essa pessoa te enviou uma solicitação.</span>
                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                                <input type="hidden" name="acao" value="aceitar">
                                <input type="hidden" name="solicitacao_id" value="<?= $statusAmizade['solicitacao_id'] ?>">
                                <button type="submit" class="btn btn--success btn--small">Aceitar</button>
                            </form>
                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                                <input type="hidden" name="acao" value="recusar">
                                <input type="hidden" name="solicitacao_id" value="<?= $statusAmizade['solicitacao_id'] ?>">
                                <button type="submit" class="btn-link-danger">Recusar</button>
                            </form>
                        <?php elseif ($statusAmizade['status'] === 'amigos'): ?>
                            <span class="texto-suave">Vocês são amigos.</span>
                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" onsubmit="return confirm('Desfazer amizade com essa pessoa?');">
                                <input type="hidden" name="acao" value="desfazer_amizade">
                                <button type="submit" class="btn-link-danger">Desfazer amizade</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <h2 class="section-title"><?= $ehProprioPerfil ? 'Minhas postagens' : 'Postagens' ?></h2>

            <?php if (!$podeVerPostagens): ?>
                <div class="card empty-state">Adicione essa pessoa como amiga para ver as postagens dela.</div>
            <?php elseif (empty($posts)): ?>
                <div class="card empty-state">Nenhuma postagem ainda.</div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="card post">

                        <div class="post__header">
                            <?= htmlFotoPerfil($usuario['foto_perfil'], 36, $usuario['nome_completo']) ?>
                            <time><?= date('d/m/Y H:i', strtotime($post['data_criacao'])) ?></time>
                        </div>

                        <div class="post__body">
                            <p><?= nl2br(htmlspecialchars($post['conteudo'])) ?></p>
                        </div>

                        <div class="post__actions">
                            <?php $jaCurtiu = usuarioCurtiu($usuarioLogadoId, $post['id']); ?>
                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                                <input type="hidden" name="acao" value="curtir">
                                <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="btn btn--success btn--small<?= $jaCurtiu ? ' is-active' : '' ?>">
                                    <?= $jaCurtiu ? 'Descurtir' : 'Curtir' ?>
                                </button>
                            </form>
                            <span class="post__likes"><?= contarCurtidas($post['id']) ?> curtida(s)</span>

                            <?php if ((int) $post['autor_id'] === $usuarioLogadoId): ?>
                                <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" onsubmit="return confirm('Excluir esta postagem?');">
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

                                            <?php if ((int) $comentario['autor_id'] === $usuarioLogadoId): ?>
                                                <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" onsubmit="return confirm('Excluir este comentário?');">
                                                    <input type="hidden" name="acao" value="excluir_comentario">
                                                    <input type="hidden" name="comentario_id" value="<?= (int) $comentario['id'] ?>">
                                                    <button type="submit" class="btn-link-danger">Excluir</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" class="comment-form">
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

    </div>

    <script src="js/orb.js"></script>

</body>
</html>