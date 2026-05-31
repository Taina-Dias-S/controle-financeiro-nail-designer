<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}

$uri_atual = $_SERVER['REQUEST_URI'];
?>

<nav class="app-bottom-nav">
    <a href="<?php echo BASE_URL; ?>dashboard.php" class="<?php echo strpos($uri_atual, 'dashboard.php') !== false ? 'active' : ''; ?>">
        <i class="bi bi-house-door"></i>
        <span>Início</span>
    </a>

    <a href="<?php echo BASE_URL; ?>lancamentos/index.php" class="<?php echo strpos($uri_atual, 'lancamentos') !== false ? 'active' : ''; ?>">
        <i class="bi bi-arrow-left-right"></i>
        <span>Lançamentos</span>
    </a>

    <a href="<?php echo BASE_URL; ?>resumo.php" class="<?php echo strpos($uri_atual, 'resumo.php') !== false ? 'active' : ''; ?>">
        <i class="bi bi-bar-chart"></i>
        <span>Resumo</span>
    </a>

    <a href="<?php echo BASE_URL; ?>logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
    </a>
</nav>