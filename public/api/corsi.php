<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

$auth = aut_service();
$data = dati_service();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'add'));

    $courseId = (int) ($_POST['course_id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $siteId = (int) ($_POST['site_id'] ?? 0);
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $userId = (int) ($_POST['user_id'] ?? 0);
    $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));
    $monthlyFeeRaw = trim((string) ($_POST['monthly_fee'] ?? ''));

    $startDate = $startDateRaw !== '' ? $startDateRaw : null;
    $monthlyFee = $monthlyFeeRaw !== '' ? (float) $monthlyFeeRaw : null;

    $orari = [
        'lun_inizio' => trim((string) ($_POST['lun_inizio'] ?? '')),
        'lun_fine' => trim((string) ($_POST['lun_fine'] ?? '')),
        'mar_inizio' => trim((string) ($_POST['mar_inizio'] ?? '')),
        'mar_fine' => trim((string) ($_POST['mar_fine'] ?? '')),
        'mer_inizio' => trim((string) ($_POST['mer_inizio'] ?? '')),
        'mer_fine' => trim((string) ($_POST['mer_fine'] ?? '')),
        'gio_inizio' => trim((string) ($_POST['gio_inizio'] ?? '')),
        'gio_fine' => trim((string) ($_POST['gio_fine'] ?? '')),
        'ven_inizio' => trim((string) ($_POST['ven_inizio'] ?? '')),
        'ven_fine' => trim((string) ($_POST['ven_fine'] ?? '')),
        'sab_inizio' => trim((string) ($_POST['sab_inizio'] ?? '')),
        'sab_fine' => trim((string) ($_POST['sab_fine'] ?? '')),
        'dom_inizio' => trim((string) ($_POST['dom_inizio'] ?? '')),
        'dom_fine' => trim((string) ($_POST['dom_fine'] ?? '')),
    ];

    if ($action === 'delete') {
        if ($courseId > 0) {
            $data->deleteCourse($courseId);
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi');
        exit;
    }

    if ($action === 'update') {
        if ($courseId > 0 && $name !== '' && $siteId > 0 && $disciplineId > 0 && $userId > 0) {
            $data->updateCourse($courseId, $siteId, $disciplineId, $userId, $name, $startDate, $monthlyFee, $orari);
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi');
        exit;
    }

    if ($name !== '' && $siteId > 0 && $disciplineId > 0 && $userId > 0) {
        $data->addCourse($siteId, $disciplineId, $userId, $name, $startDate, $monthlyFee, $orari);
    }

    header('Location: /seiryokukai_php/public/index.php?page=corsi');
    exit;
}

if (isset($_GET['draw'])) {
    $draw = (int) ($_GET['draw'] ?? 0);
    $start = (int) ($_GET['start'] ?? 0);
    $length = (int) ($_GET['length'] ?? 10);
    $search = trim((string) ($_GET['search']['value'] ?? ''));

    $orderColumnIndex = (int) ($_GET['order'][0]['column'] ?? 0);
    $orderDir = (string) ($_GET['order'][0]['dir'] ?? 'desc');
    $orderColumn = (string) ($_GET['columns'][$orderColumnIndex]['data'] ?? 'id');

    $page = $data->readCoursesPage($start, $length, $search, $orderColumn, $orderDir);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int) ($page['total'] ?? 0),
        'recordsFiltered' => (int) ($page['filtered'] ?? 0),
        'data' => $page['data'] ?? [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readCourses(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
