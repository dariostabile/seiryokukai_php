<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/data.php';

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'logout') {
    logout_user();
    header('Location: /seiryokukai_php/public/index.php?page=login');
    exit;
}

if (!is_logged_in() && $page !== 'login') {
    header('Location: /seiryokukai_php/public/index.php?page=login');
    exit;
}

if ($page === 'login') {
    require __DIR__ . '/../src/views/login.php';
    exit;
}

$user = current_user();
$pageTitle = 'Dashboard';
$viewContent = '';

if ($page === 'clients') {
    $pageTitle = 'Clienti';
    $clients = read_clients();
    ob_start();
    require __DIR__ . '/../src/views/clients.php';
    $viewContent = (string) ob_get_clean();
} else {
    $stats = dashboard_stats();
    ob_start();
    require __DIR__ . '/../src/views/dashboard.php';
    $viewContent = (string) ob_get_clean();
}

require __DIR__ . '/../src/views/layout.php';
