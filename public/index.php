<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/data.php';
require_once __DIR__ . '/../src/lib/navigation.php';

$auth = aut_service();
$data = dati_service();
$navigation = navigazione_service();
$appPaths = app_paths();
$indexPath = (string) $appPaths['index'];

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'logout') {
    $auth->logoutUser();
    header('Location: ' . $indexPath . '?page=login');
    exit;
}

if (!$auth->isLoggedIn() && $page !== 'login') {
    header('Location: ' . $indexPath . '?page=login');
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
    $atleti = [];
    $tipiDocumenti = $data->readTipiDocumenti();
    $corsi = $data->readCorsi();

    $openEditFromFlash = false;
    if (
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
        && isset($_POST['open_edit'])
        && !isset($_POST['action'])
    ) {
        $_SESSION['atleti_open_edit_flash'] = [
            'open_edit' => ((string) ($_POST['open_edit'] ?? '0')) === '1',
            'edit_id' => (int) ($_POST['edit_id'] ?? 0),
        ];

        header('Location: ' . $indexPath . '?page=atleti');
        exit;
    }

    $selectedAtletaId = (int) ($_POST['edit_id'] ?? $_GET['edit_id'] ?? 0);
    $openEditFromRequest = ((string) ($_POST['open_edit'] ?? $_GET['open_edit'] ?? '0')) === '1';

    if (!$openEditFromRequest && $selectedAtletaId <= 0 && isset($_SESSION['atleti_open_edit_flash']) && is_array($_SESSION['atleti_open_edit_flash'])) {
        $flash = $_SESSION['atleti_open_edit_flash'];
        unset($_SESSION['atleti_open_edit_flash']);

        $openEditFromFlash = ((bool) ($flash['open_edit'] ?? false)) === true;
        $selectedAtletaId = (int) ($flash['edit_id'] ?? 0);
    }

    $selectedAtleta = null;
    if ($selectedAtletaId > 0) {
        $selectedAtleta = atleti_service()->findAtletaById($selectedAtletaId);
    }
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
    $sedi = $data->readSedi();
    ob_start();
    require __DIR__ . '/../src/views/sedi.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'tipi_documento') {
    $pageTitle = 'Tipi Documento';
    $tipiDocumenti = $data->readTipiDocumenti();
    ob_start();
    require __DIR__ . '/../src/views/tipi_documento.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'disciplina') {
    $pageTitle = 'Discipline';
    $discipline = $data->readDiscipline();
    ob_start();
    require __DIR__ . '/../src/views/discipline.php';
    $viewContent = (string) ob_get_clean();
} elseif ($page === 'corsi') {
    $pageTitle = 'Corsi';
    $corsi = [];
    $sedi = $data->readSedi();
    $discipline = $data->readDiscipline();
    $users = $data->readActiveInstructors();
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
