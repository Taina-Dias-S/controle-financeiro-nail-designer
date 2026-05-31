<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$data_lancamento = $_POST['data_lancamento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$origem = $_POST['origem'] ?? '';
$id_categoria = $_POST['id_categoria'] ?? '';
$id_forma_pagamento = $_POST['id_forma_pagamento'] ?? null;
$valor = $_POST['valor'] ?? '';
$descricao = trim($_POST['descricao'] ?? '');

if (
    $id <= 0 ||
    $data_lancamento === '' ||
    $tipo === '' ||
    $origem === '' ||
    $id_categoria === '' ||
    $valor === ''
) {
    die("Preencha todos os campos obrigatórios.");
}

if (!in_array($tipo, ['entrada', 'saida'])) {
    die("Tipo inválido.");
}

if (!in_array($origem, ['negocio', 'pessoal'])) {
    die("Origem inválida.");
}

// Converte valor brasileiro para decimal do banco
$valor = str_replace('.', '', $valor);
$valor = str_replace(',', '.', $valor);
$valor = floatval($valor);

if ($valor <= 0) {
    die("O valor precisa ser maior que zero.");
}

if ($id_forma_pagamento === '') {
    $id_forma_pagamento = null;
}

try {
    // Garante que o lançamento pertence ao usuário logado
    $sqlCheck = "
        SELECT id 
        FROM lancamentos 
        WHERE id = :id 
        AND id_usuario = :id_usuario 
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([
        ':id' => $id,
        ':id_usuario' => $usuario_id
    ]);

    if (!$stmt->fetch()) {
        die("Lançamento não encontrado.");
    }

    $sql = "
        UPDATE lancamentos SET
            id_categoria = :id_categoria,
            id_forma_pagamento = :id_forma_pagamento,
            data_lancamento = :data_lancamento,
            tipo = :tipo,
            origem = :origem,
            descricao = :descricao,
            valor = :valor
        WHERE id = :id
        AND id_usuario = :id_usuario
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_categoria' => $id_categoria,
        ':id_forma_pagamento' => $id_forma_pagamento,
        ':data_lancamento' => $data_lancamento,
        ':tipo' => $tipo,
        ':origem' => $origem,
        ':descricao' => $descricao,
        ':valor' => $valor,
        ':id' => $id,
        ':id_usuario' => $usuario_id
    ]);

    header("Location: index.php?atualizado=1");
    exit;

} catch (PDOException $e) {
    die("Erro ao atualizar lançamento: " . $e->getMessage());
}