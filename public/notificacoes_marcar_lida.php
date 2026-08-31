<?php
session_start();

require_once __DIR__ . '/../models/notificacao.php';
require_once __DIR__ . '/../includes/auth.php';

exigirLogin();

marcarTodasComoLidas($_SESSION['usuario_id']);

header('Location: feed.php');
exit;