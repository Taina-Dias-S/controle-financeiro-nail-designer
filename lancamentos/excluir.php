<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    $sql = "
        DELETE FROM lancamentos
        WHERE id = :id
        AND id_usuario = :id_usuario
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':id_usuario' => $usuario_id
    ]);

    header("Location: index.php?excluido=1");
    exit;

} catch (PDOException $e) {
    die("Erro ao excluir lançamento: " . $e->getMessage());
}