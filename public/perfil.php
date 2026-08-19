<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/amizade.php';
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

// Ação de enviar solicitação direto pela página de perfil.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$ehProprioPerfil) {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar') {
        $resultado = enviarSolicitacao($usuarioLogadoId, $idVisualizado);
        $mensagem = $resultado === true ? 'Solicitação enviada!' : $resultado;
    } elseif ($acao === 'aceitar') {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        aceitarSolicitacao($solicitacaoId, $usuarioLogadoId);
    } elseif ($acao === 'recusar') {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        recusarSolicitacao($solicitacaoId, $usuarioLogadoId);
    }
}

$statusAmizade = $ehProprioPerfil ? null : verificarStatusAmizade($usuarioLogadoId, $idVisualizado);
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
        <?php endif; ?>

        <br><br>
        <a href="feed.php">Voltar ao feed</a>

    <?php endif; ?>

</body>
</html>