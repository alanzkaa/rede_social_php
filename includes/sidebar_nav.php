<?php
/**
 * Sidebar lateral esquerda — mini perfil + atalhos de navegação.
 * Espera $usuarioLogado já definido por quem inclui este arquivo.
 */
$usuarioLogado = $usuarioLogado ?? ['foto_perfil' => null, 'nome_completo' => ''];
?>
<aside class="sidebar sidebar--left">
    <div class="sidebar__profile">
        <?= htmlFotoPerfil($usuarioLogado['foto_perfil'], 64, $usuarioLogado['nome_completo']) ?>
        <strong><?= htmlspecialchars($usuarioLogado['nome_completo']) ?></strong>
    </div>
    <nav class="sidebar__nav">
        <a href="perfil.php">Meu perfil</a>
        <a href="perfil_editar.php">Editar perfil</a>
        <a href="comunidade.php">Comunidade</a>
        <a href="amigos.php">Amigos</a>
        <a href="configuracoes.php">Configurações</a>
        <a href="logout.php">Sair</a>
    </nav>
</aside>