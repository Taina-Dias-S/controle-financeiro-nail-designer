<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meu Caixa Nail</title>

<meta name="description" content="Sistema simples de controle financeiro para nail designer.">
<meta name="theme-color" content="#c9a27b">

<link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
<link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>assets/img/icon-192.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('<?php echo BASE_URL; ?>service-worker.js')
            .catch(function (error) {
                console.log('Erro ao registrar Service Worker:', error);
            });
    });
}
</script>