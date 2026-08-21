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

// Ações vindas de formulários (amizade, curtir, comentar).
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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $ehProprioPerfil ? 'Meu perfil' : htmlspecialchars($usuario['nome_completo']) ?></title>
</head>
<body>

    <h1><?= $ehProprioPerfil ? 'Meu perfil' : htmlspecialchars($usuario['nome_completo']) ?></h1>

    <?= htmlFotoPerfil($usuario['foto_perfil'], 120) ?><br><br>

    <?php if ($mensagem): ?>
        <p><strong><?= htmlspecialchars($mensagem) ?></strong></p>
    <?php endif; ?>

    <p><strong>Nome completo:</strong> <?= htmlspecialchars($usuario['nome_completo']) ?></p>

    <?php if ($ehProprioPerfil): ?>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
    <?php endif; ?>

    <p><strong>Nome de usuário:</strong>
        <?= $usuario['nome_usuario'] ? htmlspecialchars($usuario['nome_usuario']) : '(não definido)' ?>
    </p>

    <p><strong>Data de nascimento:</strong>
        <?= $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : '(não informada)' ?>
    </p>

    <p><strong>Membro desde:</strong>
        <?= date('d/m/Y', strtotime($usuario['data_cadastro'])) ?>
    </p>

    <?php if ($ehProprioPerfil): ?>

        <a href="perfil_editar.php">Editar perfil</a>
        |
        <a href="feed.php">Voltar ao feed</a>

    <?php else: ?>

        <?php if ($statusAmizade['status'] === 'nenhuma'): ?>
            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                <input type="hidden" name="acao" value="enviar">
                <button type="submit">Adicionar amigo</button>
            </form>
        <?php elseif ($statusAmizade['status'] === 'pendente_enviada'): ?>
            <p><em>Solicitação enviada, aguardando resposta.</em></p>
        <?php elseif ($statusAmizade['status'] === 'pendente_recebida'): ?>
            <p><em>Essa pessoa te enviou uma solicitação de amizade.</em></p>
            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" style="display:inline;">
                <input type="hidden" name="acao" value="aceitar">
                <input type="hidden" name="solicitacao_id" value="<?= $statusAmizade['solicitacao_id'] ?>">
                <button type="submit">Aceitar</button>
            </form>
            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" style="display:inline;">
                <input type="hidden" name="acao" value="recusar">
                <input type="hidden" name="solicitacao_id" value="<?= $statusAmizade['solicitacao_id'] ?>">
                <button type="submit">Recusar</button>
            </form>
        <?php elseif ($statusAmizade['status'] === 'amigos'): ?>
            <p><em>Vocês são amigos.</em></p>
            <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" onsubmit="return confirm('Desfazer amizade com essa pessoa?');">
                <input type="hidden" name="acao" value="desfazer_amizade">
                <button type="submit">Desfazer amizade</button>
            </form>
        <?php endif; ?>

        <br><br>
        <a href="feed.php">Voltar ao feed</a>

    <?php endif; ?>

    <hr>

    <h2><?= $ehProprioPerfil ? 'Minhas postagens' : 'Postagens' ?></h2>

    <?php if (!$podeVerPostagens): ?>
        <p><em>Adicione essa pessoa como amiga para ver as postagens dela.</em></p>
    <?php elseif (empty($posts)): ?>
        <p>Nenhuma postagem ainda.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <small><?= date('d/m/Y H:i', strtotime($post['data_criacao'])) ?></small>
                <p><?= nl2br(htmlspecialchars($post['conteudo'])) ?></p>

                <?php $jaCurtiu = usuarioCurtiu($usuarioLogadoId, $post['id']); ?>
                <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" style="display:inline;">
                    <input type="hidden" name="acao" value="curtir">
                    <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                    <button type="submit"><?= $jaCurtiu ? 'Descurtir' : 'Curtir' ?></button>
                </form>
                <?= contarCurtidas($post['id']) ?> curtida(s)

                <?php if ((int) $post['autor_id'] === $usuarioLogadoId): ?>
                    <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" style="display:inline;" onsubmit="return confirm('Excluir esta postagem?');">
                        <input type="hidden" name="acao" value="excluir_post">
                        <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                        <button type="submit">Excluir</button>
                    </form>
                <?php endif; ?>

                <div style="margin-left:20px; margin-top:10px;">
                    <?php foreach (listarComentarios($post['id']) as $comentario): ?>
                        <p>
                            <?= htmlFotoPerfil($comentario['foto_perfil'], 24) ?>
                            <a href="perfil.php?id=<?= (int) $comentario['autor_id'] ?>"><strong><?= htmlspecialchars($comentario['nome_completo']) ?>:</strong></a>
                            <?= htmlspecialchars($comentario['conteudo']) ?>
                            <br>
                            <small><?= date('d/m/Y H:i', strtotime($comentario['data_criacao'])) ?></small>

                            <?php if ((int) $comentario['autor_id'] === $usuarioLogadoId): ?>
                                <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>" style="display:inline;" onsubmit="return confirm('Excluir este comentário?');">
                                    <input type="hidden" name="acao" value="excluir_comentario">
                                    <input type="hidden" name="comentario_id" value="<?= (int) $comentario['id'] ?>">
                                    <button type="submit">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>

                    <form method="POST" action="perfil.php?id=<?= $idVisualizado ?>">
                        <input type="hidden" name="acao" value="comentar">
                        <input type="hidden" name="postagem_id" value="<?= (int) $post['id'] ?>">
                        <input type="text" name="conteudo_comentario" placeholder="Escreva um comentário..." size="40">
                        <button type="submit">Comentar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>