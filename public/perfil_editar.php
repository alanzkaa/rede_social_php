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
    $nomeCompleto   = trim($_POST['nome_completo'] ?? '');
    $nomeUsuario    = trim($_POST['nome_usuario'] ?? '') ?: null;
    $dataNascimento = $_POST['data_nascimento'] ?? null;

    if ($nomeCompleto === '') {
        $erro = 'O nome completo é obrigatório.';
    } else {
        $resultado = atualizarPerfil($usuarioId, $nomeCompleto, $nomeUsuario, $dataNascimento ?: null);

        if ($resultado === true) {
            $sucesso = true;
            // Recarrega os dados atualizados para exibir no formulário.
            $usuario = buscarUsuarioPorId($usuarioId);
        } else {
            $erro = $resultado;
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

    <form method="POST" action="perfil_editar.php">
        <label>Nome completo:<br>
            <input type="text" name="nome_completo" value="<?= htmlspecialchars($usuario['nome_completo']) ?>" required>
        </label><br><br>

        <label>Nome de usuário:<br>
            <input type="text" name="nome_usuario" value="<?= htmlspecialchars($usuario['nome_usuario'] ?? '') ?>">
        </label><br><br>

        <label>Data de nascimento:<br>
            <input type="date" name="data_nascimento" value="<?= htmlspecialchars($usuario['data_nascimento'] ?? '') ?>">
        </label><br><br>

        <p><em>E-mail: <?= htmlspecialchars($usuario['email']) ?> (não editável)</em></p>

        <button type="submit">Salvar alterações</button>
    </form>

    <a href="perfil.php">Voltar ao perfil</a>

</body>
</html>