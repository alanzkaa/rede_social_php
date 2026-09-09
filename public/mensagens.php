<?php
session_start();

require_once __DIR__ . '/../models/mensagem.php';
require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$conversas = listarConversas($usuarioId);
$usuarioLogado = buscarUsuarioPorId($usuarioId);
$paginaAtual = 'mensagens';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Mensagens</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <section class="card card--padded">
                <h2 class="section-title">Mensagens</h2>

                <?php if (empty($conversas)): ?>
                    <p class="texto-suave">Você ainda não tem conversas. Visite o perfil de um amigo e clique em "Mensagem" pra começar uma.</p>
                <?php else: ?>
                    <ul class="conversa-list">
                        <?php foreach ($conversas as $c): ?>
                            <li>
                                <a href="conversa.php?com=<?= (int) $c['outro_id'] ?>" class="conversa-item">
                                    <?= htmlAvatarComStatus(htmlFotoPerfil($c['foto_perfil'], 44, $c['nome_completo']), $c['ultima_atividade']) ?>
                                    <div class="conversa-item__info">
                                        <div class="conversa-item__nome"><?= htmlspecialchars($c['nome_completo']) ?></div>
                                        <p class="conversa-item__snippet"><?= htmlspecialchars($c['ultima_mensagem'] ?? '') ?></p>
                                    </div>
                                    <div class="conversa-item__meta">
                                        <span><?= date('d/m H:i', strtotime($c['ultima_data'])) ?></span>
                                        <?php if ((int) $c['nao_lidas'] > 0): ?>
                                            <span class="badge-count"><?= (int) $c['nao_lidas'] > 9 ? '9+' : (int) $c['nao_lidas'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
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