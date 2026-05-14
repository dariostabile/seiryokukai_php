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
    $type = trim((string) ($_POST['type'] ?? ''));

    if ($type !== '') {
        $data->addDocumentType($type);
    }

    header('Location: /seiryokukai_php/public/index.php?page=tipi_documento');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readDocumentTypes(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
