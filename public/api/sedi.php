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
    $code = trim((string) ($_POST['code'] ?? ''));

    if ($name !== '') {
        $data->addSite($name, $code);
    }

    header('Location: /seiryokukai_php/public/index.php?page=sedi');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readSites(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
