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

    if ($action === 'update') {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $siteId = (int) ($_POST['site_id'] ?? 0);
        $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));
        $monthlyFeeRaw = trim((string) ($_POST['monthly_fee'] ?? ''));

        $startDate = $startDateRaw !== '' ? $startDateRaw : null;
        $monthlyFee = $monthlyFeeRaw !== '' ? (float) $monthlyFeeRaw : null;

        $days = [
            'lun' => isset($_POST['day_lun']),
            'mar' => isset($_POST['day_mar']),
            'merc' => isset($_POST['day_merc']),
            'giov' => isset($_POST['day_giov']),
            'ven' => isset($_POST['day_ven']),
            'sab' => isset($_POST['day_sab']),
            'dom' => isset($_POST['day_dom']),
        ];

        if ($courseId > 0 && $name !== '' && $siteId > 0 && $disciplineId > 0 && $userId > 0) {
            $data->updateCourse($courseId, $siteId, $disciplineId, $userId, $name, $startDate, $monthlyFee, $days);
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi');
        exit;
    }

    if ($action === 'delete') {
        $courseId = (int) ($_POST['course_id'] ?? 0);

        if ($courseId > 0) {
            $data->deleteCourse($courseId);
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi');
        exit;
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $siteId = (int) ($_POST['site_id'] ?? 0);
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $userId = (int) ($_POST['user_id'] ?? 0);
    $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));
    $monthlyFeeRaw = trim((string) ($_POST['monthly_fee'] ?? ''));

    $startDate = $startDateRaw !== '' ? $startDateRaw : null;
    $monthlyFee = $monthlyFeeRaw !== '' ? (float) $monthlyFeeRaw : null;

    $days = [
        'lun' => isset($_POST['day_lun']),
        'mar' => isset($_POST['day_mar']),
        'merc' => isset($_POST['day_merc']),
        'giov' => isset($_POST['day_giov']),
        'ven' => isset($_POST['day_ven']),
        'sab' => isset($_POST['day_sab']),
        'dom' => isset($_POST['day_dom']),
    ];

    if ($name !== '' && $siteId > 0 && $disciplineId > 0 && $userId > 0) {
        $data->addCourse($siteId, $disciplineId, $userId, $name, $startDate, $monthlyFee, $days);
    }

    header('Location: /seiryokukai_php/public/index.php?page=corsi');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readCourses(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
