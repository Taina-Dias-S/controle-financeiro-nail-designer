<?php
// config/conexao.php

$host = "HOST_DO_BANCO";
$banco = "NOME_DO_BANCO";
$usuario = "USUARIO_DO_BANCO";
$senha = "SENHA_DO_BANCO";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

