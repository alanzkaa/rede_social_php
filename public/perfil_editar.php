<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar perfil</title>
</head>
<body>

    <h1>Editar perfil</h1>

    <?php if ($sucesso): ?>
        <p style="color:green;">Perfil atualizado com sucesso!</p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <h2>Foto de perfil</h2>

    <?php if ($usuario['foto_perfil']): ?>
        <img src="uploads/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil" width="120"><br>
    <?php else: ?>
        <p>(Nenhuma foto definida)</p>
    <?php endif; ?>

    <form method="POST" action="perfil_editar.php" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="foto">
        <input type="file" name="foto" accept="image/jpeg,image/png,image/gif" required>
        <button type="submit">Enviar foto</button>
    </form>

    <hr>

    <h2>Dados pessoais</h2>

    <form method="POST" action="perfil_editar.php">
        <input type="hidden" name="acao" value="dados">

        <label>Nome completo:<br>
            <input type="text" name="nome_completo" value="<?= htmlspecialchars($usuario['nome_completo']) ?>" required>
        </label><br><br>

        <label>Nome de usuário:<br>
            <input type="text" name="nome_usuario" value="<?= htmlspecialchars($usuario['nome_usuario'] ?? '') ?>">
        </label><br><br>

        <label>Data de nascimento:<br>
            <input type="date" name="data_nascimento" value="<?= htmlspecialchars($usuario['data_nascimento'] ?? '') ?>">
        </label><br><br>

        <p><em>E-mail: <?= htmlspecialchars($usuario['email']) ?> (não editável por aqui)</em></p>

        <button type="submit">Salvar alterações</button>
    </form>

    <a href="perfil.php">Voltar ao perfil</a>

</body>
</html>