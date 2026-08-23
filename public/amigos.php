<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/amizade.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$mensagem = null;

// Ações vindas de formulários (enviar, aceitar, recusar) chegam via POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar') {
        $amigoId = (int) ($_POST['amigo_id'] ?? 0);
        $resultado = enviarSolicitacao($usuarioId, $amigoId);
        $mensagem = $resultado === true ? 'Solicitação enviada!' : $resultado;
    }

    if ($acao === 'aceitar') {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        $ok = aceitarSolicitacao($solicitacaoId, $usuarioId);
        $mensagem = $ok ? 'Solicitação aceita!' : 'Não foi possível aceitar essa solicitação.';
    }

    if ($acao === 'recusar') {
        $solicitacaoId = (int) ($_POST['solicitacao_id'] ?? 0);
        $ok = recusarSolicitacao($solicitacaoId, $usuarioId);
        $mensagem = $ok ? 'Solicitação recusada.' : 'Não foi possível recusar essa solicitação.';
    }

    if ($acao === 'desfazer_amizade') {
        $amigoId = (int) ($_POST['amigo_id'] ?? 0);
        $ok = desfazerAmizade($usuarioId, $amigoId);
        $mensagem = $ok ? 'Amizade desfeita.' : 'Não foi possível desfazer a amizade.';
    }
}

// Busca de usuários (GET, via campo de pesquisa).
$termoBusca = trim($_GET['busca'] ?? '');
$resultadosBusca = $termoBusca !== '' ? buscarUsuarios($termoBusca, $usuarioId) : [];

$pendentes = listarSolicitacoesPendentes($usuarioId);
$amigos = listarAmigos($usuarioId);
$usuarioLogado = buscarUsuarioPorId($usuarioId);
$paginaAtual = 'amigos';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Amigos</title>
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

            <section class="card card--padded">
                <h2 class="section-title">Buscar pessoas</h2>

                <form method="GET" action="amigos.php" class="search-form">
                    <input type="text" name="busca" placeholder="Nome ou @usuário" value="<?= htmlspecialchars($termoBusca) ?>">
                    <button type="submit" class="btn btn--primary btn--small">Buscar</button>
                </form>

                <?php if ($termoBusca !== ''): ?>
                    <?php if (empty($resultadosBusca)): ?>
                        <p class="texto-suave" style="margin-top:14px;">Nenhum usuário encontrado.</p>
                    <?php else: ?>
                        <ul class="people-list">
                            <?php foreach ($resultadosBusca as $pessoa): ?>
                                <li class="people-item">
                                    <?= htmlFotoPerfil($pessoa['foto_perfil'], 40, $pessoa['nome_completo']) ?>
                                    <span class="people-item__name">
                                        <?= htmlspecialchars($pessoa['nome_completo']) ?>
                                        <?php if ($pessoa['nome_usuario']): ?>
                                            (@<?= htmlspecialchars($pessoa['nome_usuario']) ?>)
                                        <?php endif; ?>
                                    </span>
                                    <form method="POST" action="amigos.php" class="people-item__actions">
                                        <input type="hidden" name="acao" value="enviar">
                                        <input type="hidden" name="amigo_id" value="<?= (int) $pessoa['id'] ?>">
                                        <button type="submit" class="btn btn--primary btn--small">Adicionar</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="card card--padded">
                <h2 class="section-title">Solicitações pendentes</h2>

                <?php if (empty($pendentes)): ?>
                    <p class="texto-suave">Nenhuma solicitação pendente.</p>
                <?php else: ?>
                    <ul class="people-list">
                        <?php foreach ($pendentes as $p): ?>
                            <li class="people-item">
                                <?= htmlFotoPerfil($p['foto_perfil'], 40, $p['nome_completo']) ?>
                                <span class="people-item__name">
                                    <?= htmlspecialchars($p['nome_completo']) ?>
                                    <?php if ($p['nome_usuario']): ?>
                                        (@<?= htmlspecialchars($p['nome_usuario']) ?>)
                                    <?php endif; ?>
                                </span>
                                <div class="people-item__actions">
                                    <form method="POST" action="amigos.php">
                                        <input type="hidden" name="acao" value="aceitar">
                                        <input type="hidden" name="solicitacao_id" value="<?= (int) $p['solicitacao_id'] ?>">
                                        <button type="submit" class="btn btn--success btn--small">Aceitar</button>
                                    </form>
                                    <form method="POST" action="amigos.php">
                                        <input type="hidden" name="acao" value="recusar">
                                        <input type="hidden" name="solicitacao_id" value="<?= (int) $p['solicitacao_id'] ?>">
                                        <button type="submit" class="btn-link-danger">Recusar</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="card card--padded">
                <h2 class="section-title">Meus amigos</h2>

                <?php if (empty($amigos)): ?>
                    <p class="texto-suave">Você ainda não tem amigos adicionados.</p>
                <?php else: ?>
                    <ul class="people-list">
                        <?php foreach ($amigos as $amigo): ?>
                            <li class="people-item">
                                <?= htmlFotoPerfil($amigo['foto_perfil'], 40, $amigo['nome_completo']) ?>
                                <span class="people-item__name">
                                    <a href="perfil.php?id=<?= (int) $amigo['id'] ?>"><?= htmlspecialchars($amigo['nome_completo']) ?></a>
                                    <?php if ($amigo['nome_usuario']): ?>
                                        (@<?= htmlspecialchars($amigo['nome_usuario']) ?>)
                                    <?php endif; ?>
                                </span>
                                <form method="POST" action="amigos.php" class="people-item__actions" onsubmit="return confirm('Desfazer amizade com essa pessoa?');">
                                    <input type="hidden" name="acao" value="desfazer_amizade">
                                    <input type="hidden" name="amigo_id" value="<?= (int) $amigo['id'] ?>">
                                    <button type="submit" class="btn-link-danger">Desfazer amizade</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        </main>

    </div>

    <script src="js/orb.js"></script>

</body>
</html>