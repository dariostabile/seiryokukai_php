<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/data.php';
require_once __DIR__ . '/../src/lib/navigation.php';

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'atleti') {
    $page = 'clients';
}

if ($page === 'sedi') {
    $page = 'sites';
}

if ($page === 'tipi_documento') {
    $page = 'document_types';
}

if ($page === 'disciplina' || $page === 'discipline') {
    $page = 'disciplines';
}

if ($page === 'corsi') {
    $page = 'courses';
}

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
$menuGroups = navigation_menu_for_user((int) ($user['id'] ?? 0));
$currentPage = $page;
$pageTitle = 'Dashboard';
$viewContent = '';

if ($page === 'clients') {
    $pageTitle = 'Atleti';
    $clients = read_clients();
    ob_start();
    require __DIR__ . '/../src/views/clients.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'users') {
    $pageTitle = 'Utenti';
    $users = read_users();
    ob_start();
    require __DIR__ . '/../src/views/users.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'sites') {
    $pageTitle = 'Sedi';
    $sites = read_sites();
    ob_start();
    require __DIR__ . '/../src/views/sites.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'document_types') {
    $pageTitle = 'Tipi Documento';
    $documentTypes = read_document_types();
    ob_start();
    require __DIR__ . '/../src/views/document_types.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'disciplines') {
    $pageTitle = 'Discipline';
    $disciplines = read_disciplines();
    ob_start();
    require __DIR__ . '/../src/views/disciplines.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'courses') {
    $pageTitle = 'Corsi';
    $courses = read_courses();
    $sites = read_sites();
    $disciplines = read_disciplines();
    $users = read_users();
    ob_start();
    require __DIR__ . '/../src/views/courses.php';
    $viewContent = (string) ob_get_clean();
} else {
    $stats = dashboard_stats();
    ob_start();
    require __DIR__ . '/../src/views/dashboard.php';
    $viewContent = (string) ob_get_clean();
}

require __DIR__ . '/../src/views/layout.php';
