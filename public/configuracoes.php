<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$usuario = buscarUsuarioPorId($usuarioId);

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'senha') {
        $senhaAtual     = $_POST['senha_atual'] ?? '';
        $senhaNova      = $_POST['senha_nova'] ?? '';
        $senhaConfirmar = $_POST['senha_confirmar'] ?? '';

        if ($senhaNova !== $senhaConfirmar) {
            $erro = 'A confirmação não bate com a nova senha.';
        } else {
            $resultado = alterarSenha($usuarioId, $senhaAtual, $senhaNova);

            if ($resultado === true) {
                $sucesso = 'Senha alterada com sucesso!';
            } else {
                $erro = $resultado;
            }
        }
    } elseif ($acao === 'privacidade') {
        $privacidade = ($_POST['privacidade_postagens'] ?? 'amigos') === 'publico' ? 'publico' : 'amigos';
        $aceitaSolicitacoes = isset($_POST['aceita_solicitacoes']);

        atualizarPrivacidade($usuarioId, $privacidade, $aceitaSolicitacoes);
        $sucesso = 'Privacidade atualizada!';
        $usuario = buscarUsuarioPorId($usuarioId);
    } elseif ($acao === 'notificacoes') {
        atualizarPreferenciasNotificacao(
            $usuarioId,
            isset($_POST['notif_curtida']),
            isset($_POST['notif_comentario']),
            isset($_POST['notif_solicitacao_amizade']),
            isset($_POST['notif_amizade_aceita'])
        );
        $sucesso = 'Preferências de notificação atualizadas!';
        $usuario = buscarUsuarioPorId($usuarioId);
    } elseif ($acao === 'excluir_conta') {
        $senha = $_POST['senha_exclusao'] ?? '';
        $resultado = excluirConta($usuarioId, $senha);

        if ($resultado === true) {
            session_destroy();
            header('Location: login.php?conta_excluida=1');
            exit;
        } else {
            $erro = $resultado;
        }
    }
}

$usuarioLogado = $usuario;
$paginaAtual = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Configurações</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <?php if ($sucesso): ?>
                <p class="alert-success"><?= htmlspecialchars($sucesso) ?></p>
            <?php endif; ?>

            <?php if ($erro): ?>
                <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <section class="card card--padded">
                <h2 class="section-title">Segurança</h2>

                <form method="POST" action="configuracoes.php">
                    <input type="hidden" name="acao" value="senha">

                    <div class="campo">
                        <label for="senha_atual">Senha atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" required>
                    </div>

                    <div class="campo">
                        <label for="senha_nova">Nova senha</label>
                        <input type="password" id="senha_nova" name="senha_nova" required minlength="6">
                    </div>

                    <div class="campo">
                        <label for="senha_confirmar">Confirmar nova senha</label>
                        <input type="password" id="senha_confirmar" name="senha_confirmar" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn--primary btn--small">Trocar senha</button>
                </form>
            </section>

            <section class="card card--padded">
                <h2 class="section-title">Privacidade</h2>

                <form method="POST" action="configuracoes.php">
                    <input type="hidden" name="acao" value="privacidade">

                    <p class="texto-suave" style="margin-top:0;">Quem pode ver minhas postagens</p>

                    <div class="campo-radio">
                        <input type="radio" id="priv_amigos" name="privacidade_postagens" value="amigos" <?= $usuario['privacidade_postagens'] === 'amigos' ? 'checked' : '' ?>>
                        <label for="priv_amigos">Só meus amigos</label>
                    </div>
                    <div class="campo-radio">
                        <input type="radio" id="priv_publico" name="privacidade_postagens" value="publico" <?= $usuario['privacidade_postagens'] === 'publico' ? 'checked' : '' ?>>
                        <label for="priv_publico">Todo mundo</label>
                    </div>

                    <div class="campo-checkbox" style="margin-top:16px;">
                        <input type="checkbox" id="aceita_solicitacoes" name="aceita_solicitacoes" value="1" <?= $usuario['aceita_solicitacoes'] ? 'checked' : '' ?>>
                        <label for="aceita_solicitacoes">Receber solicitações de amizade</label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--small" style="margin-top:6px;">Salvar privacidade</button>
                </form>
            </section>

            <section class="card card--padded">
                <h2 class="section-title">Notificações</h2>

                <form method="POST" action="configuracoes.php">
                    <input type="hidden" name="acao" value="notificacoes">

                    <div class="campo-checkbox">
                        <input type="checkbox" id="notif_curtida" name="notif_curtida" value="1" <?= $usuario['notif_curtida'] ? 'checked' : '' ?>>
                        <label for="notif_curtida">Quando alguém curtir minha postagem</label>
                    </div>
                    <div class="campo-checkbox">
                        <input type="checkbox" id="notif_comentario" name="notif_comentario" value="1" <?= $usuario['notif_comentario'] ? 'checked' : '' ?>>
                        <label for="notif_comentario">Quando alguém comentar na minha postagem</label>
                    </div>
                    <div class="campo-checkbox">
                        <input type="checkbox" id="notif_solicitacao_amizade" name="notif_solicitacao_amizade" value="1" <?= $usuario['notif_solicitacao_amizade'] ? 'checked' : '' ?>>
                        <label for="notif_solicitacao_amizade">Quando eu receber uma solicitação de amizade</label>
                    </div>
                    <div class="campo-checkbox">
                        <input type="checkbox" id="notif_amizade_aceita" name="notif_amizade_aceita" value="1" <?= $usuario['notif_amizade_aceita'] ? 'checked' : '' ?>>
                        <label for="notif_amizade_aceita">Quando alguém aceitar minha solicitação de amizade</label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--small" style="margin-top:6px;">Salvar notificações</button>
                </form>
            </section>

            <section class="card card--padded card--perigo">
                <h2 class="section-title">Zona de perigo</h2>
                <p class="texto-suave">Excluir sua conta é permanente. Todas as suas postagens, comentários, curtidas, amizades e notificações serão apagadas junto.</p>

                <form method="POST" action="configuracoes.php" onsubmit="return confirm('Tem certeza que quer excluir sua conta? Essa ação não pode ser desfeita.');">
                    <input type="hidden" name="acao" value="excluir_conta">

                    <div class="campo">
                        <label for="senha_exclusao">Digite sua senha para confirmar</label>
                        <input type="password" id="senha_exclusao" name="senha_exclusao" required>
                    </div>

                    <button type="submit" class="btn--danger">Excluir minha conta</button>
                </form>
            </section>

        </main>

    </div>

    <script src="js/orb.js"></script>

</body>
</html>