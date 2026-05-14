<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/data.php';
require_once __DIR__ . '/../src/lib/navigation.php';

$auth = aut_service();
$data = dati_service();
$navigation = navigazione_service();

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
    $clients = [];
    ob_start();
    require __DIR__ . '/../src/views/atleti.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'utenti') {
    $pageTitle = 'Utenti';
    $users = [];
    $profiles = $data->readProfiles();
      $applicationsCatalog = $data->readApplicationsCatalog();
    $currentUserId = (int) ($user['id'] ?? 0);
    ob_start();
    require __DIR__ . '/../src/views/utenti.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'sedi') {
    $pageTitle = 'Sedi';
    $sites = $data->readSites();
    ob_start();
    require __DIR__ . '/../src/views/sedi.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'tipi_documento') {
    $pageTitle = 'Tipi Documento';
    $documentTypes = $data->readDocumentTypes();
    ob_start();
    require __DIR__ . '/../src/views/tipi_documento.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'disciplina') {
    $pageTitle = 'Discipline';
    $disciplines = $data->readDisciplines();
    ob_start();
    require __DIR__ . '/../src/views/discipline.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'corsi') {
    $pageTitle = 'Corsi';
    $courses = [];
    $sites = $data->readSites();
    $disciplines = $data->readDisciplines();
    $users = $data->readUsers();
    ob_start();
    require __DIR__ . '/../src/views/corsi.php';
    $viewContent = (string) ob_get_clean();
} else {
    $stats = $data->dashboardStats();
    ob_start();
    require __DIR__ . '/../src/views/dashboard.php';
    $viewContent = (string) ob_get_clean();
}

require __DIR__ . '/../src/views/layout.php';
