<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$busca = trim($_GET['busca'] ?? '');

$data_inicio = "$ano-$mes-01";
$data_fim = date("Y-m-t", strtotime($data_inicio));

$params = [
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
];

$sql = "
    SELECT 
        l.*,
        c.nome AS categoria,
        f.nome AS forma_pagamento
    FROM lancamentos l
    INNER JOIN categorias c ON c.id = l.id_categoria
    LEFT JOIN formas_pagamento f ON f.id = l.id_forma_pagamento
    WHERE l.id_usuario = :id_usuario
    AND l.data_lancamento BETWEEN :data_inicio AND :data_fim
";

if ($busca !== '') {
    $sql .= " AND (
        l.descricao LIKE :busca
        OR c.nome LIKE :busca
        OR f.nome LIKE :busca
    )";

    $params[':busca'] = '%' . $busca . '%';
}

$sql .= " ORDER BY l.data_lancamento DESC, l.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lancamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totais do período filtrado
$total_entradas = 0;
$total_saidas = 0;

foreach ($lancamentos as $item) {
    if ($item['tipo'] === 'entrada') {
        $total_entradas += (float) $item['valor'];
    } else {
        $total_saidas += (float) $item['valor'];
    }
}

$saldo_periodo = $total_entradas - $total_saidas;

function moeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include '../includes/head.php'; ?>
</head>
<body class="app-body">

<main class="app-container">

    <div class="app-header">
        <div>
            <span class="small text-muted">Controle</span>
            <h1>Lançamentos</h1>
        </div>

        <a href="cadastrar.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">
            Lançamento cadastrado com sucesso.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['atualizado'])): ?>
        <div class="alert alert-success">
            Lançamento atualizado com sucesso.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['excluido'])): ?>
        <div class="alert alert-success">
            Lançamento excluído com sucesso.
        </div>
    <?php endif; ?>

    <section class="lancamentos-summary">
        <div class="mini-summary-card">
            <span>Entradas</span>
            <strong class="value-positive"><?php echo moeda($total_entradas); ?></strong>
        </div>

        <div class="mini-summary-card">
            <span>Saídas</span>
            <strong class="value-negative"><?php echo moeda($total_saidas); ?></strong>
        </div>

        <div class="mini-summary-card">
            <span>Saldo</span>
            <strong class="<?php echo $saldo_periodo >= 0 ? 'value-positive' : 'value-negative'; ?>">
                <?php echo moeda($saldo_periodo); ?>
            </strong>
        </div>
    </section>

    <form method="GET" class="filter-card lancamentos-filter">
        <div>
            <label>Mês</label>
            <select name="mes" class="form-select">
                <?php for ($i = 1; $i <= 12; $i++): 
                    $valorMes = str_pad($i, 2, '0', STR_PAD_LEFT);
                ?>
                    <option value="<?php echo $valorMes; ?>" <?php echo $mes == $valorMes ? 'selected' : ''; ?>>
                        <?php echo $valorMes; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label>Ano</label>
            <input type="number" name="ano" class="form-control" value="<?php echo htmlspecialchars($ano); ?>">
        </div>

        <div>
            <label>Buscar</label>
            <input 
                type="text" 
                name="busca" 
                class="form-control" 
                placeholder="Descrição, categoria..." 
                value="<?php echo htmlspecialchars($busca); ?>"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Filtrar
        </button>

        <a href="index.php" class="btn btn-light btn-clear-filter">
            Limpar
        </a>
    </form>

    <div class="lancamentos-actions">
        <a href="cadastrar.php" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg"></i>
            Novo lançamento
        </a>
    </div>

    <section class="section-box">

        <div class="section-title">
            <h2>Movimentações</h2>
            <span class="small text-muted"><?php echo count($lancamentos); ?> registro(s)</span>
        </div>

        <?php if (count($lancamentos) > 0): ?>

            <div class="transaction-list">
                <?php foreach ($lancamentos as $lancamento): ?>
                    <div class="transaction-item transaction-item-full">

                        <div class="transaction-main">
                            <strong>
                                <?php echo htmlspecialchars($lancamento['categoria']); ?>
                            </strong>

                            <span>
                                <?php echo date('d/m/Y', strtotime($lancamento['data_lancamento'])); ?>
                                •
                                <?php echo ucfirst($lancamento['origem']); ?>
                                •
                                <?php echo htmlspecialchars($lancamento['forma_pagamento'] ?? 'Sem forma'); ?>
                            </span>

                            <?php if (!empty($lancamento['descricao'])): ?>
                                <small>
                                    <?php echo htmlspecialchars($lancamento['descricao']); ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="transaction-side">
                            <div class="<?php echo $lancamento['tipo'] === 'entrada' ? 'value-positive' : 'value-negative'; ?>">
                                <?php echo $lancamento['tipo'] === 'entrada' ? '+' : '-'; ?>
                                <?php echo moeda($lancamento['valor']); ?>
                            </div>

                            <div class="transaction-actions">
                                <a href="editar.php?id=<?php echo $lancamento['id']; ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="excluir.php?id=<?php echo $lancamento['id']; ?>" 
                                   class="btn btn-sm btn-light text-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este lançamento?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <p>Nenhum lançamento encontrado.</p>
                <a href="cadastrar.php" class="btn btn-primary btn-sm">Adicionar lançamento</a>
            </div>

        <?php endif; ?>

    </section>

</main>

<?php include '../includes/menu.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>