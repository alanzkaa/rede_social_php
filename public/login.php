<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';

// Se já está logado, não faz sentido ver a tela de login de novo.
if (usuarioLogado()) {
    header('Location: feed.php');
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $usuario = buscarUsuarioPorEmail($email);

        // password_verify compara a senha digitada com o hash salvo no banco.
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Regenera o ID de sessão por segurança (evita session fixation).
            session_regenerate_id(true);

            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome_completo'];

            header('Location: feed.php');
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Entrar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

    <div class="card auth-card">
        <div class="auth-card__brand">☁ BlueSpace</div>
        <h1>Entrar</h1>

        <?php if ($erro): ?>
            <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn btn--primary">Entrar</button>
        </form>

        <p class="auth-card__footer">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>

</body>
</html>