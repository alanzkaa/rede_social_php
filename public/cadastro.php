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
    <title>Cadastro</title>
</head>
<body>

    <h1>Criar conta</h1>

    <?php if ($sucesso): ?>
        <p>Cadastro realizado com sucesso! <a href="login.php">Fazer login</a></p>
    <?php else: ?>

        <?php if ($erro): ?>
            <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            <label>Nome completo:<br>
                <input type="text" name="nome_completo" required>
            </label><br><br>

            <label>E-mail:<br>
                <input type="email" name="email" required>
            </label><br><br>

            <label>Senha:<br>
                <input type="password" name="senha" required minlength="6">
            </label><br><br>

            <label>Nome de usuário (opcional):<br>
                <input type="text" name="nome_usuario">
            </label><br><br>

            <label>Data de nascimento:<br>
                <input type="date" name="data_nascimento">
            </label><br><br>

            <button type="submit">Cadastrar</button>
        </form>

    <?php endif; ?>

</body>
</html>