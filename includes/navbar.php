<?php
/**
 * Barra de navegação superior — reutilizada em todas as páginas logadas.
 * Quem incluir este arquivo precisa ter definido antes:
 * - $usuarioLogado : array com os dados do usuário (de buscarUsuarioPorId)
 * - $paginaAtual    : 'feed' ou 'amigos', opcional, só pra destacar o link ativo
 */
require_once __DIR__ . '/../models/notificacao.php';

$paginaAtual = $paginaAtual ?? '';
$usuarioLogado = $usuarioLogado ?? ['id' => 0, 'foto_perfil' => null, 'nome_completo' => ''];

$notificacoes = listarNotificacoes($usuarioLogado['id']);
$naoLidas = contarNaoLidas($usuarioLogado['id']);
?>
<header class="navbar">
    <span class="navbar__brand">☁ BlueSpace</span>

    <nav class="navbar__nav">
        <a href="feed.php" class="<?= $paginaAtual === 'feed' ? 'is-active' : '' ?>">Início</a>
        <a href="amigos.php" class="<?= $paginaAtual === 'amigos' ? 'is-active' : '' ?>">Amigos</a>
    </nav>

    <details class="notif-menu">
        <summary class="notif-bell">
            🔔
            <?php if ($naoLidas > 0): ?>
                <span class="notif-badge"><?= $naoLidas > 9 ? '9+' : $naoLidas ?></span>
            <?php endif; ?>
        </summary>
        <div class="notif-dropdown">
            <div class="notif-dropdown__header">
                <strong>Notificações</strong>
                <?php if ($naoLidas > 0): ?>
                    <a href="notificacoes_marcar_lida.php">Marcar tudo como lida</a>
                <?php endif; ?>
            </div>

            <?php if (empty($notificacoes)): ?>
                <p class="texto-suave" style="padding:14px;">Nenhuma notificação ainda.</p>
            <?php else: ?>
                <?php foreach ($notificacoes as $n): ?>
                    <a href="<?= linkNotificacao($n['tipo'], (int) $n['ator_id']) ?>"
                       class="notif-item<?= !$n['lida'] ? ' notif-item--nao-lida' : '' ?>">
                        <?= htmlFotoPerfil($n['ator_foto'], 32, $n['ator_nome']) ?>
                        <span class="notif-item__texto">
                            <?= textoNotificacao($n['tipo'], $n['ator_nome']) ?>
                            <span class="notif-item__data"><?= date('d/m/Y H:i', strtotime($n['data_criacao'])) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </details>

    <details class="orb-menu">
        <summary class="orb">
            <?= htmlFotoPerfil($usuarioLogado['foto_perfil'], 40, $usuarioLogado['nome_completo']) ?>
        </summary>
        <div class="orb-dropdown">
            <a href="perfil.php">Meu perfil</a>
            <a href="perfil_editar.php">Editar perfil</a>
            <a href="amigos.php">Amigos</a>
            <a href="logout.php">Sair</a>
        </div>
    </details>
</header>