<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

$auth = auth_service();
$data = data_service();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $siteId = (int) ($_POST['site_id'] ?? 0);
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $userId = (int) ($_POST['user_id'] ?? 0);
    $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));

    $startDate = $startDateRaw !== '' ? $startDateRaw : null;

    if ($name !== '' && $siteId > 0 && $disciplineId > 0 && $userId > 0) {
        $data->addCourse($siteId, $disciplineId, $userId, $name, $startDate, null);
    }

    header('Location: /seiryokukai_php/public/index.php?page=corsi');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readCourses(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
