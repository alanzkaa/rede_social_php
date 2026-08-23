<?php
/**
 * Barra de navegação superior — reutilizada em todas as páginas logadas.
 * Quem incluir este arquivo precisa ter definido antes:
 * - $usuarioLogado : array com os dados do usuário (de buscarUsuarioPorId)
 * - $paginaAtual    : 'feed' ou 'amigos', opcional, só pra destacar o link ativo
 */
$paginaAtual = $paginaAtual ?? '';
$usuarioLogado = $usuarioLogado ?? ['foto_perfil' => null, 'nome_completo' => ''];
?>
<header class="navbar">
    <span class="navbar__brand">☁ BlueSpace</span>

    <nav class="navbar__nav">
        <a href="feed.php" class="<?= $paginaAtual === 'feed' ? 'is-active' : '' ?>">Início</a>
        <a href="amigos.php" class="<?= $paginaAtual === 'amigos' ? 'is-active' : '' ?>">Amigos</a>
    </nav>

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