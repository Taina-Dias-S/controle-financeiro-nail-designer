<?php
require_once 'config/app.php';
require_once 'includes/proteger.php';
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$data_inicio = "$ano-$mes-01";
$data_fim = date("Y-m-t", strtotime($data_inicio));

function moeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function percentual($parte, $total) {
    if ($total <= 0) {
        return 0;
    }

    return round(($parte / $total) * 100);
}

// Totais principais
$sqlTotais = "
    SELECT
        COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) AS entradas,
        COALESCE(SUM(CASE WHEN tipo = 'saida' AND origem = 'negocio' THEN valor ELSE 0 END), 0) AS gastos_negocio,
        COALESCE(SUM(CASE WHEN tipo = 'saida' AND origem = 'pessoal' THEN valor ELSE 0 END), 0) AS gastos_pessoais
    FROM lancamentos
    WHERE id_usuario = :id_usuario
    AND data_lancamento BETWEEN :data_inicio AND :data_fim
";

$stmt = $pdo->prepare($sqlTotais);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);

$totais = $stmt->fetch(PDO::FETCH_ASSOC);

$entradas = (float) $totais['entradas'];
$gastos_negocio = (float) $totais['gastos_negocio'];
$gastos_pessoais = (float) $totais['gastos_pessoais'];

$lucro_real = $entradas - $gastos_negocio;
$sobrou_mes = $lucro_real - $gastos_pessoais;
$total_saidas = $gastos_negocio + $gastos_pessoais;

// Entradas por categoria
$sqlEntradasCategoria = "
    SELECT 
        c.nome AS categoria,
        COALESCE(SUM(l.valor), 0) AS total
    FROM lancamentos l
    INNER JOIN categorias c ON c.id = l.id_categoria
    WHERE l.id_usuario = :id_usuario
    AND l.tipo = 'entrada'
    AND l.data_lancamento BETWEEN :data_inicio AND :data_fim
    GROUP BY c.id, c.nome
    ORDER BY total DESC
";

$stmt = $pdo->prepare($sqlEntradasCategoria);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);

$entradas_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Saídas por categoria
$sqlSaidasCategoria = "
    SELECT 
        c.nome AS categoria,
        l.origem,
        COALESCE(SUM(l.valor), 0) AS total
    FROM lancamentos l
    INNER JOIN categorias c ON c.id = l.id_categoria
    WHERE l.id_usuario = :id_usuario
    AND l.tipo = 'saida'
    AND l.data_lancamento BETWEEN :data_inicio AND :data_fim
    GROUP BY c.id, c.nome, l.origem
    ORDER BY total DESC
";

$stmt = $pdo->prepare($sqlSaidasCategoria);
$stmt->execute([
    ':id_usuario' => $usuario_id,
    ':data_inicio' => $data_inicio,
    ':data_fim' => $data_fim
]);

$saidas_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Maior gasto
$maior_gasto = $saidas_categoria[0] ?? null;

// Texto de análise simples
if ($entradas <= 0) {
    $analise = "Ainda não há entradas registradas neste mês. Comece cadastrando os atendimentos ou pacotes recebidos.";
    $analise_classe = "alert-info";
} elseif ($sobrou_mes > 0) {
    $analise = "O mês está positivo. Depois dos gastos do negócio e dos gastos pessoais, ainda sobrou dinheiro.";
    $analise_classe = "alert-success";
} elseif ($sobrou_mes == 0) {
    $analise = "O mês está no equilíbrio. As entradas cobriram os gastos, mas não houve sobra.";
    $analise_classe = "alert-warning";
} else {
    $analise = "O mês está negativo. Os gastos ficaram maiores que o dinheiro disponível no período.";
    $analise_classe = "alert-danger";
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
            <span class="small text-muted">Análise mensal</span>
            <h1>Resumo</h1>
        </div>

        <div class="header-icon">
            <i class="bi bi-bar-chart"></i>
        </div>
    </div>

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

    <div class="alert <?php echo $analise_classe; ?> resumo-alert">
        <?php echo $analise; ?>
    </div>

    <section class="cards-grid">

        <div class="finance-card card-green">
            <span>Entradas</span>
            <strong><?php echo moeda($entradas); ?></strong>
            <small>Total recebido no mês</small>
        </div>

        <div class="finance-card card-red">
            <span>Gastos do negócio</span>
            <strong><?php echo moeda($gastos_negocio); ?></strong>
            <small><?php echo percentual($gastos_negocio, $entradas); ?>% das entradas</small>
        </div>

        <div class="finance-card card-orange">
            <span>Gastos pessoais</span>
            <strong><?php echo moeda($gastos_pessoais); ?></strong>
            <small><?php echo percentual($gastos_pessoais, $entradas); ?>% das entradas</small>
        </div>

        <div class="finance-card card-purple">
            <span>Lucro real</span>
            <strong><?php echo moeda($lucro_real); ?></strong>
            <small>Entradas menos gastos do negócio</small>
        </div>

        <div class="finance-card card-dark">
            <span>Sobrou no mês</span>
            <strong><?php echo moeda($sobrou_mes); ?></strong>
            <small>Lucro real menos gastos pessoais</small>
        </div>

    </section>

    <?php if ($maior_gasto): ?>
        <section class="section-box resumo-highlight">
            <div>
                <span class="small text-muted">Maior gasto do mês</span>
                <h2><?php echo htmlspecialchars($maior_gasto['categoria']); ?></h2>
                <p>
                    <?php echo moeda($maior_gasto['total']); ?> em gastos de 
                    <?php echo $maior_gasto['origem'] === 'negocio' ? 'negócio' : 'uso pessoal'; ?>.
                </p>
            </div>

            <div class="highlight-icon">
                <i class="bi bi-arrow-down-circle"></i>
            </div>
        </section>
    <?php endif; ?>

    <section class="section-box mb-3">
        <div class="section-title">
            <h2>Entradas por categoria</h2>
        </div>

        <?php if (count($entradas_categoria) > 0): ?>
            <div class="resumo-list">
                <?php foreach ($entradas_categoria as $item): 
                    $porcentagem = percentual($item['total'], $entradas);
                ?>
                    <div class="resumo-line">
                        <div class="resumo-line-top">
                            <strong><?php echo htmlspecialchars($item['categoria']); ?></strong>
                            <span><?php echo moeda($item['total']); ?></span>
                        </div>

                        <div class="progress resumo-progress">
                            <div 
                                class="progress-bar" 
                                role="progressbar" 
                                style="width: <?php echo $porcentagem; ?>%;"
                                aria-valuenow="<?php echo $porcentagem; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>

                        <small><?php echo $porcentagem; ?>% das entradas</small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-cash-stack"></i>
                <p>Nenhuma entrada registrada neste mês.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-box">
        <div class="section-title">
            <h2>Gastos por categoria</h2>
        </div>

        <?php if (count($saidas_categoria) > 0): ?>
            <div class="resumo-list">
                <?php foreach ($saidas_categoria as $item): 
                    $porcentagem = percentual($item['total'], $total_saidas);
                ?>
                    <div class="resumo-line">
                        <div class="resumo-line-top">
                            <div>
                                <strong><?php echo htmlspecialchars($item['categoria']); ?></strong>
                                <small>
                                    <?php echo $item['origem'] === 'negocio' ? 'Negócio' : 'Pessoal'; ?>
                                </small>
                            </div>

                            <span><?php echo moeda($item['total']); ?></span>
                        </div>

                        <div class="progress resumo-progress">
                            <div 
                                class="progress-bar" 
                                role="progressbar" 
                                style="width: <?php echo $porcentagem; ?>%;"
                                aria-valuenow="<?php echo $porcentagem; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>

                        <small><?php echo $porcentagem; ?>% dos gastos</small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <p>Nenhum gasto registrado neste mês.</p>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include 'includes/menu.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>