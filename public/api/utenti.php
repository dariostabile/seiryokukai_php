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
    $action = trim((string) ($_POST['action'] ?? ''));
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'status' && $id > 0) {
        $status = trim((string) ($_POST['status'] ?? ''));
        $data->updateUserStatus($id, $status);
    }

    header('Location: /seiryokukai_php/public/index.php?page=utenti');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readUsers(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
