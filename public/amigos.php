<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/amizade.php';
require_once __DIR__ . '/../includes/auth.php';

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Amigos</title>
</head>
<body>

    <h1>Amigos</h1>

    <?php if ($mensagem): ?>
        <p><strong><?= htmlspecialchars($mensagem) ?></strong></p>
    <?php endif; ?>

    <h2>Buscar pessoas</h2>
    <form method="GET" action="amigos.php">
        <input type="text" name="busca" placeholder="Nome ou @usuário" value="<?= htmlspecialchars($termoBusca) ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($termoBusca !== ''): ?>
        <?php if (empty($resultadosBusca)): ?>
            <p>Nenhum usuário encontrado.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($resultadosBusca as $pessoa): ?>
                    <li>
                        <?= htmlspecialchars($pessoa['nome_completo']) ?>
                        <?php if ($pessoa['nome_usuario']): ?>
                            (@<?= htmlspecialchars($pessoa['nome_usuario']) ?>)
                        <?php endif; ?>
                        <form method="POST" action="amigos.php" style="display:inline;">
                            <input type="hidden" name="acao" value="enviar">
                            <input type="hidden" name="amigo_id" value="<?= (int) $pessoa['id'] ?>">
                            <button type="submit">Adicionar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Solicitações pendentes</h2>
    <?php if (empty($pendentes)): ?>
        <p>Nenhuma solicitação pendente.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($pendentes as $p): ?>
                <li>
                    <?= htmlspecialchars($p['nome_completo']) ?>
                    <?php if ($p['nome_usuario']): ?>
                        (@<?= htmlspecialchars($p['nome_usuario']) ?>)
                    <?php endif; ?>

                    <form method="POST" action="amigos.php" style="display:inline;">
                        <input type="hidden" name="acao" value="aceitar">
                        <input type="hidden" name="solicitacao_id" value="<?= (int) $p['solicitacao_id'] ?>">
                        <button type="submit">Aceitar</button>
                    </form>

                    <form method="POST" action="amigos.php" style="display:inline;">
                        <input type="hidden" name="acao" value="recusar">
                        <input type="hidden" name="solicitacao_id" value="<?= (int) $p['solicitacao_id'] ?>">
                        <button type="submit">Recusar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Meus amigos</h2>
    <?php if (empty($amigos)): ?>
        <p>Você ainda não tem amigos adicionados.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($amigos as $amigo): ?>
                <li>
                    <a href="perfil.php?id=<?= (int) $amigo['id'] ?>"><?= htmlspecialchars($amigo['nome_completo']) ?></a>
                    <?php if ($amigo['nome_usuario']): ?>
                        (@<?= htmlspecialchars($amigo['nome_usuario']) ?>)
                    <?php endif; ?>
                    <form method="POST" action="amigos.php" style="display:inline;" onsubmit="return confirm('Desfazer amizade com essa pessoa?');">
                        <input type="hidden" name="acao" value="desfazer_amizade">
                        <input type="hidden" name="amigo_id" value="<?= (int) $amigo['id'] ?>">
                        <button type="submit">Desfazer amizade</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="feed.php">Voltar ao feed</a>

</body>
</html>