<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$data_lancamento = $_POST['data_lancamento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$origem = $_POST['origem'] ?? '';
$id_categoria = $_POST['id_categoria'] ?? '';
$id_forma_pagamento = $_POST['id_forma_pagamento'] ?? null;
$valor = $_POST['valor'] ?? '';
$descricao = trim($_POST['descricao'] ?? '');

if (
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

// Converte valor brasileiro para formato decimal do banco
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
    $sql = "
        INSERT INTO lancamentos (
            id_usuario,
            id_categoria,
            id_forma_pagamento,
            data_lancamento,
            tipo,
            origem,
            descricao,
            valor
        ) VALUES (
            :id_usuario,
            :id_categoria,
            :id_forma_pagamento,
            :data_lancamento,
            :tipo,
            :origem,
            :descricao,
            :valor
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $usuario_id,
        ':id_categoria' => $id_categoria,
        ':id_forma_pagamento' => $id_forma_pagamento,
        ':data_lancamento' => $data_lancamento,
        ':tipo' => $tipo,
        ':origem' => $origem,
        ':descricao' => $descricao,
        ':valor' => $valor
    ]);

    header("Location: index.php?sucesso=1");
    exit;

} catch (PDOException $e) {
    die("Erro ao salvar lançamento: " . $e->getMessage());
}