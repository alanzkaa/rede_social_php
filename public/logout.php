<?php
session_start();

// Limpa todas as variáveis de sessão e destrói a sessão no servidor.
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;