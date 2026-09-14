<?php
session_start();

require_once __DIR__ . '/../models/mensagem.php';
require_once __DIR__ . '/../models/amizade.php';
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$outroId = (int) ($_GET['com'] ?? 0);

$outro = buscarUsuarioPorId($outroId);

// Não existe conversa consigo mesmo, nem com um usuário que não existe.
if (!$outro || $outroId === $usuarioId) {
    header('Location: mensagens.php');
    exit;
}

// Só é permitido conversar com amigos.
$status = verificarStatusAmizade($usuarioId, $outroId)['status'];

if ($status !== 'amigos') {
    header('Location: perfil.php?id=' . $outroId);
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'enviar') {
    $conteudo = $_POST['conteudo'] ?? '';
    $resultado = enviarMensagem($usuarioId, $outroId, $conteudo);

    if ($resultado !== true) {
        $erro = $resultado;
    }
}

$conversaId = buscarConversaEntre($usuarioId, $outroId);
$mensagens = $conversaId ? listarMensagens($conversaId) : [];

// Marca como lidas as mensagens que a outra pessoa mandou, já que estamos vendo a conversa agora.
if ($conversaId) {
    marcarConversaComoLida($conversaId, $usuarioId);
}

$usuarioLogado = buscarUsuarioPorId($usuarioId);
$paginaAtual = 'conversa';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Conversa com <?= htmlspecialchars($outro['nome_completo']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= ($usuarioLogado['tema'] ?? 'claro') === 'escuro' ? 'tema-escuro' : '' ?>">

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <?php if ($erro): ?>
                <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <section class="card">
                <div class="chat-header">
                    <?= htmlAvatarComStatus(htmlFotoPerfil($outro['foto_perfil'], 40, $outro['nome_completo']), $outro['ultima_atividade']) ?>
                    <div>
                        <a href="perfil.php?id=<?= $outroId ?>"><strong><?= htmlspecialchars($outro['nome_completo']) ?></strong></a>
                        <div class="texto-suave" style="font-size:0.78rem;"><?= textoUltimaAtividade($outro['ultima_atividade']) ?></div>
                    </div>
                </div>

                <div class="chat-thread">
                    <?php if (empty($mensagens)): ?>
                        <p class="texto-suave" style="text-align:center;">Nenhuma mensagem ainda. Diga oi!</p>
                    <?php else: ?>
                        <?php foreach ($mensagens as $m): ?>
                            <div class="chat-bubble <?= (int) $m['remetente_id'] === $usuarioId ? 'chat-bubble--minha' : 'chat-bubble--outro' ?>">
                                <?= nl2br(htmlspecialchars($m['conteudo'])) ?>
                                <time><?= date('d/m/Y H:i', strtotime($m['data_envio'])) ?></time>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form method="POST" action="conversa.php?com=<?= $outroId ?>" class="chat-compose">
                    <input type="hidden" name="acao" value="enviar">
                    <input type="text" name="conteudo" placeholder="Escreva uma mensagem..." autofocus>
                    <button type="submit" class="btn btn--primary btn--small">Enviar</button>
                </form>
            </section>

        </main>

    </div>

    <script src="js/orb.js"></script>

</body>
</html>