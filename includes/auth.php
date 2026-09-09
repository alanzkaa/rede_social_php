<?php
/**
 * Funções auxiliares de autenticação/sessão.
 * Todo arquivo que usar essas funções precisa ter session_start() já chamado.
 */
require_once __DIR__ . '/../models/usuario.php';

function usuarioLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Bloqueia o acesso à página se o usuário não estiver logado,
 * redirecionando para o login. Se estiver logado, aproveita e atualiza
 * a última atividade dele — é assim que o status "online" funciona,
 * sem precisar tocar em cada página individualmente.
 */
function exigirLogin(): void
{
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }

    atualizarUltimaAtividade($_SESSION['usuario_id']);
}