<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioId = $_SESSION['usuario_id'];
$usuario = buscarUsuarioPorId($usuarioId);

$erro = null;
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'foto') {
        $resultadoFoto = atualizarFotoPerfil($usuarioId, $_FILES['foto']);

        if ($resultadoFoto === true) {
            $sucesso = true;
            $usuario = buscarUsuarioPorId($usuarioId);
        } else {
            $erro = $resultadoFoto;
        }
    } else {
        $nomeCompleto   = trim($_POST['nome_completo'] ?? '');
        $nomeUsuario    = trim($_POST['nome_usuario'] ?? '') ?: null;
        $dataNascimento = $_POST['data_nascimento'] ?? null;

        if ($nomeCompleto === '') {
            $erro = 'O nome completo é obrigatório.';
        } else {
            $resultado = atualizarPerfil($usuarioId, $nomeCompleto, $nomeUsuario, $dataNascimento ?: null);

            if ($resultado === true) {
                $sucesso = true;
                $usuario = buscarUsuarioPorId($usuarioId);
            } else {
                $erro = $resultado;
            }
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
    <title>BlueSpace · Editar perfil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">

        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">

            <?php if ($sucesso): ?>
                <p class="alert-success">Perfil atualizado com sucesso!</p>
            <?php endif; ?>

            <?php if ($erro): ?>
                <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <section class="card card--padded">
                <h2 class="section-title">Foto de perfil</h2>

                <div class="foto-atual">
                    <?= htmlFotoPerfil($usuario['foto_perfil'], 72, $usuario['nome_completo']) ?>

                    <form method="POST" action="perfil_editar.php" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center;">
                        <input type="hidden" name="acao" value="foto">
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/gif" required>
                        <button type="submit" class="btn btn--primary btn--small">Enviar foto</button>
                    </form>
                </div>
                <p class="texto-suave">JPG, PNG ou GIF, até 2MB.</p>
            </section>

            <section class="card card--padded">
                <h2 class="section-title">Dados pessoais</h2>

                <form method="POST" action="perfil_editar.php">
                    <input type="hidden" name="acao" value="dados">

                    <div class="campo">
                        <label for="nome_completo">Nome completo</label>
                        <input type="text" id="nome_completo" name="nome_completo" value="<?= htmlspecialchars($usuario['nome_completo']) ?>" required>
                    </div>

                    <div class="campo">
                        <label for="nome_usuario">Nome de usuário</label>
                        <input type="text" id="nome_usuario" name="nome_usuario" value="<?= htmlspecialchars($usuario['nome_usuario'] ?? '') ?>">
                    </div>

                    <div class="campo">
                        <label for="data_nascimento">Data de nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($usuario['data_nascimento'] ?? '') ?>">
                    </div>

                    <p class="texto-suave">E-mail: <?= htmlspecialchars($usuario['email']) ?> (não editável por aqui)</p>

                    <button type="submit" class="btn btn--primary">Salvar alterações</button>
                </form>
            </section>

        </main>

    </div>

    <script src="js/orb.js"></script>

</body>
</html>