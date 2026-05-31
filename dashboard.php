<?php
require_once 'config/app.php';
require_once 'includes/proteger.php';
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$data_inicio = "$ano-$mes-01";
$data_fim = date("Y-m-t", strtotime($data_inicio));

// Entradas do negócio
$sqlEntradas = "
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM lancamentos
    WHERE id_usuario = :id_usuario
    AND tipo = 'entrada'
    AND data_lancamento BETWEEN :data_inicio AND :data_fim
";
$stmt = $pdo->prepare($sqlEntradas);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);
$entradas = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Gastos do negócio
$sqlGastosNegocio = "
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM lancamentos
    WHERE id_usuario = :id_usuario
    AND tipo = 'saida'
    AND origem = 'negocio'
    AND data_lancamento BETWEEN :data_inicio AND :data_fim
";
$stmt = $pdo->prepare($sqlGastosNegocio);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);
$gastos_negocio = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Gastos pessoais
$sqlGastosPessoais = "
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM lancamentos
    WHERE id_usuario = :id_usuario
    AND tipo = 'saida'
    AND origem = 'pessoal'
    AND data_lancamento BETWEEN :data_inicio AND :data_fim
";
$stmt = $pdo->prepare($sqlGastosPessoais);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);
$gastos_pessoais = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$lucro_real = $entradas - $gastos_negocio;
$sobrou_mes = $entradas - $gastos_negocio - $gastos_pessoais;

if ($entradas <= 0) {
    $status_mes = "Sem entradas";
    $mensagem_mes = "Ainda não há recebimentos cadastrados neste mês.";
    $status_classe = "status-neutral";
} elseif ($sobrou_mes > 0) {
    $status_mes = "Mês positivo";
    $mensagem_mes = "Depois dos gastos do negócio e pessoais, ainda sobrou dinheiro.";
    $status_classe = "status-positive";
} elseif ($sobrou_mes == 0) {
    $status_mes = "Mês equilibrado";
    $mensagem_mes = "As entradas cobriram os gastos, mas não houve sobra.";
    $status_classe = "status-warning";
} else {
    $status_mes = "Mês negativo";
    $mensagem_mes = "Os gastos ficaram maiores que o dinheiro disponível no mês.";
    $status_classe = "status-negative";
}

// Últimos lançamentos
$sqlUltimos = "
    SELECT l.*, c.nome AS categoria
    FROM lancamentos l
    INNER JOIN categorias c ON c.id = l.id_categoria
    WHERE l.id_usuario = :id_usuario
    AND l.data_lancamento BETWEEN :data_inicio AND :data_fim
    ORDER BY l.data_lancamento DESC, l.id DESC
    LIMIT 5
";
$stmt = $pdo->prepare($sqlUltimos);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);
$ultimos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function moeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body class="app-body">

    <main class="app-container">

        <div class="app-header">
            <div>
                <span class="small text-muted">Olá,</span>
                <h1><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></h1>
            </div>

            <div class="header-icon">
                <i class="bi bi-person-circle"></i>
            </div>
        </div>

<section class="balance-hero <?php echo $status_classe; ?>">
    <div>
        <span>Saldo do mês</span>
        <h2><?php echo moeda($sobrou_mes); ?></h2>
        <strong><?php echo $status_mes; ?></strong>
        <p><?php echo $mensagem_mes; ?></p>
    </div>

    <div class="balance-hero-icon">
        <?php if ($sobrou_mes > 0): ?>
            <i class="bi bi-arrow-up-circle"></i>
        <?php elseif ($sobrou_mes < 0): ?>
            <i class="bi bi-arrow-down-circle"></i>
        <?php else: ?>
            <i class="bi bi-dash-circle"></i>
        <?php endif; ?>
    </div>
</section>




        <section class="quick-actions">
    <a href="lancamentos/cadastrar.php?tipo=entrada&origem=negocio" class="quick-action-card">
        <div class="quick-action-icon icon-green">
            <i class="bi bi-plus-lg"></i>
        </div>
        <div>
            <strong>Entrada</strong>
            <span>Registrar recebimento</span>
        </div>
    </a>

    <a href="lancamentos/cadastrar.php?tipo=saida&origem=negocio" class="quick-action-card">
        <div class="quick-action-icon icon-red">
            <i class="bi bi-briefcase"></i>
        </div>
        <div>
            <strong>Gasto negócio</strong>
            <span>Material, taxa ou custo</span>
        </div>
    </a>

    <a href="lancamentos/cadastrar.php?tipo=saida&origem=pessoal" class="quick-action-card">
        <div class="quick-action-icon icon-orange">
            <i class="bi bi-person"></i>
        </div>
        <div>
            <strong>Gasto pessoal</strong>
            <span>Despesa do dia a dia</span>
        </div>
    </a>
</section>

        <form method="GET" class="filter-card">
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

            <button type="submit" class="btn btn-primary">
                Filtrar
            </button>
        </form>

        <section class="cards-grid">

            <div class="finance-card card-green">
                <span>Entradas</span>
                <strong><?php echo moeda($entradas); ?></strong>
                <small>Total recebido no mês</small>
            </div>

            <div class="finance-card card-red">
                <span>Gastos do negócio</span>
                <strong><?php echo moeda($gastos_negocio); ?></strong>
                <small>Custos para trabalhar</small>
            </div>

            <div class="finance-card card-orange">
                <span>Gastos pessoais</span>
                <strong><?php echo moeda($gastos_pessoais); ?></strong>
                <small>Despesas pessoais</small>
            </div>

            <div class="finance-card card-purple">
                <span>Lucro real</span>
                <strong><?php echo moeda($lucro_real); ?></strong>
                <small>Entradas menos gastos do negócio</small>
            </div>


        </section>

        <section class="section-box">
            <div class="section-title">
                <h2>Últimos lançamentos</h2>
                <a href="lancamentos/index.php">Ver todos</a>
            </div>

            <?php if (count($ultimos) > 0): ?>
                <div class="transaction-list">
                    <?php foreach ($ultimos as $lancamento): ?>
                        <div class="transaction-item">
                            <div>
                                <strong><?php echo htmlspecialchars($lancamento['categoria']); ?></strong>
                                <span>
                                    <?php echo date('d/m/Y', strtotime($lancamento['data_lancamento'])); ?>
                                    •
                                    <?php echo htmlspecialchars($lancamento['descricao'] ?? 'Sem descrição'); ?>
                                </span>
                            </div>

                            <div class="<?php echo $lancamento['tipo'] === 'entrada' ? 'value-positive' : 'value-negative'; ?>">
                                <?php echo $lancamento['tipo'] === 'entrada' ? '+' : '-'; ?>
                                <?php echo moeda($lancamento['valor']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <p>Nenhum lançamento encontrado neste mês.</p>
                    <a href="lancamentos/cadastrar.php" class="btn btn-primary btn-sm">Adicionar lançamento</a>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <?php include 'includes/menu.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>