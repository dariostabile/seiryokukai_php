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
    $action = trim((string) ($_POST['action'] ?? 'add'));
    $name = trim((string) ($_POST['name'] ?? ''));
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'status' && $id > 0) {
        $status = trim((string) ($_POST['status'] ?? ''));
        $data->updateClientStatus($id, $status);
        header('Location: /seiryokukai_php/public/index.php?page=atleti');
        exit;
    }

    if ($action === 'delete' && $id > 0) {
        $data->deleteClient($id);
        header('Location: /seiryokukai_php/public/index.php?page=atleti');
        exit;
    }

    if ($action === 'add' && $name !== '') {
        $data->addClient($name);
    }

    header('Location: /seiryokukai_php/public/index.php?page=atleti');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data->readClients(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
