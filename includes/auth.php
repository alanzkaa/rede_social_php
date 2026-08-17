<?php
/**
 * Funções auxiliares de autenticação/sessão.
 * Todo arquivo que usar essas funções precisa ter session_start() já chamado.
 */

function usuarioLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Bloqueia o acesso à página se o usuário não estiver logado,
 * redirecionando para o login.
 */
function exigirLogin(): void
{
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }
}