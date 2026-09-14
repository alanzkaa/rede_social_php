<?php
session_start();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$usuarioLogado = buscarUsuarioPorId($_SESSION['usuario_id']);
$paginaAtual = 'sobre';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlueSpace · Sobre</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= ($usuarioLogado['tema'] ?? 'claro') === 'escuro' ? 'tema-escuro' : '' ?>">

    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <div class="layout layout--2col">
        <?php require __DIR__ . '/../includes/sidebar_nav.php'; ?>

        <main class="feed">
            <section class="card card--padded">
                <h1 style="margin-top:0;">Sobre o BlueSpace</h1>
                <p>O BlueSpace é uma rede social construída do zero em PHP puro, sem framework, como projeto de aprendizado</p>
                <p>O projeto foi desenvolvido progressivamente, implementando todas as funcionalidades manualmente: autenticação, perfis, amizades, feed, curtidas, comentários, notificações, mensagens privadas, status online e muito mais.</p>

                <h2>Sobre o autor</h2>
                <p>Desenvolvido por <strong>Alan</strong>, estudante do curso Jovem Programador no SENAC, com interesse em desenvolvimento web, design retro e infraestrutura.</p>

                <ul>
                    <li>Email: <a href="mailto:alanrochapereira7@gmail.com">alanrochapereira7@gmail.com</a></li>
                    <li>GitHub: <a href="https://github.com/alanzkaa" target="_blank">github.com/alanzkaa</a></li>
                    <li>LinkedIn: <a href="https://linkedin.com/in/alan-osvaldo-rocha-pereira" target="_blank">linkedin.com/in/alan-osvaldo-rocha-pereira</a></li>
                </ul>

                <h2>Tecnologias usadas</h2>
                <ul>
                    <li>PHP 8 puro (sem framework)</li>
                    <li>MySQL via PDO (prepared statements)</li>
                    <li>HTML, CSS e JavaScript puro</li>
                    <li>Identidade visual inspirada no Frutiger Aero / Windows XP–Vista</li>
                    <li>XAMPP como ambiente de desenvolvimento</li>
                </ul>

                <h2>Versão</h2>
                <p>BlueSpace v1.0 — <?= date('Y') ?></p>
            </section>
        </main>
    </div>

    <script src="js/orb.js"></script>
</body>
</html>