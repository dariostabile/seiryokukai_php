<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/data.php';
require_once __DIR__ . '/../src/lib/navigation.php';

$auth = auth_service();
$data = data_service();
$navigation = navigation_service();

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'logout') {
    $auth->logoutUser();
    header('Location: /seiryokukai_php/public/index.php?page=login');
    exit;
}

if (!$auth->isLoggedIn() && $page !== 'login') {
    header('Location: /seiryokukai_php/public/index.php?page=login');
    exit;
}

if ($page === 'login') {
    require __DIR__ . '/../src/views/login.php';
    exit;
}

$user = $auth->currentUser();
$menuGroups = $navigation->navigationMenuForUser((int) ($user['id'] ?? 0));
$currentPage = $page;
$pageTitle = 'Dashboard';
$viewContent = '';

if ($page === 'atleti') {
    $pageTitle = 'Atleti';
    $clients = $data->readClients();
    ob_start();
    require __DIR__ . '/../src/views/clients.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'utenti') {
    $pageTitle = 'Utenti';
    $users = $data->readUsers();
    ob_start();
    require __DIR__ . '/../src/views/users.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'sedi') {
    $pageTitle = 'Sedi';
    $sites = $data->readSites();
    ob_start();
    require __DIR__ . '/../src/views/sites.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'tipi_documento') {
    $pageTitle = 'Tipi Documento';
    $documentTypes = $data->readDocumentTypes();
    ob_start();
    require __DIR__ . '/../src/views/document_types.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'disciplina') {
    $pageTitle = 'Discipline';
    $disciplines = $data->readDisciplines();
    ob_start();
    require __DIR__ . '/../src/views/disciplines.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'corsi') {
    $pageTitle = 'Corsi';
    $courses = $data->readCourses();
    $sites = $data->readSites();
    $disciplines = $data->readDisciplines();
    $users = $data->readUsers();
    ob_start();
    require __DIR__ . '/../src/views/courses.php';
    $viewContent = (string) ob_get_clean();
} else {
    $stats = $data->dashboardStats();
    ob_start();
    require __DIR__ . '/../src/views/dashboard.php';
    $viewContent = (string) ob_get_clean();
}

require __DIR__ . '/../src/views/layout.php';
