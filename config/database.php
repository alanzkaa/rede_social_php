<?php
/**
 * Conexão com o banco de dados via PDO.
 * XAMPP padrão: usuário "root", sem senha, porta 3306.
 */

function conectar(): PDO
{
    $host = 'localhost';
    $porta = '3306';
    $banco = 'db_rede_social';
    $usuario = 'root';
    $senha = '';

    $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4";

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $usuario, $senha, $opcoes);
    } catch (PDOException $e) {
        // Em produção nunca exiba $e->getMessage() direto pro usuário.
        // Por enquanto, em desenvolvimento, ajuda a entender o erro.
        die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
    }
}