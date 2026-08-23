<?php
require_once __DIR__ . '/../models/usuario.php';

$erro = null;
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeCompleto   = trim($_POST['nome_completo'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $senha          = $_POST['senha'] ?? '';
    $nomeUsuario    = trim($_POST['nome_usuario'] ?? '') ?: null;
    $dataNascimento = $_POST['data_nascimento'] ?? null;

    // Validação básica antes de chamar a função de banco.
    if ($nomeCompleto === '' || $email === '' || $senha === '') {
        $erro = 'Nome, e-mail e senha são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } else {
        $resultado = cadastrarUsuario($nomeCompleto, $email, $senha, $nomeUsuario, $dataNascimento ?: null);

        if ($resultado === true) {
            $sucesso = true;
        } else {
            $erro = $resultado; // função devolveu a mensagem de erro (ex: e-mail duplicado)
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Criar conta</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

    <div class="card auth-card">
        <div class="auth-card__brand">☁ BlueSpace</div>

        <?php if ($sucesso): ?>
            <h1>Conta criada!</h1>
            <p class="alert-success">Cadastro realizado com sucesso! <a href="login.php">Fazer login</a></p>
        <?php else: ?>
            <h1>Criar conta</h1>

            <?php if ($erro): ?>
                <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="POST" action="cadastro.php">
                <div class="campo">
                    <label for="nome_completo">Nome completo</label>
                    <input type="text" id="nome_completo" name="nome_completo" required>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required minlength="6">
                </div>

                <div class="campo">
                    <label for="nome_usuario">Nome de usuário (opcional)</label>
                    <input type="text" id="nome_usuario" name="nome_usuario">
                </div>

                <div class="campo">
                    <label for="data_nascimento">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento">
                </div>

                <button type="submit" class="btn btn--primary">Cadastrar</button>
            </form>
        <?php endif; ?>

        <p class="auth-card__footer">Já tem conta? <a href="login.php">Entrar</a></p>
    </div>

</body>
</html>