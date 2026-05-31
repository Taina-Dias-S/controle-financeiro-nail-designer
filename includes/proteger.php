<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}