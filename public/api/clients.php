<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

if (!is_logged_in()) {
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
        update_client_status($id, $status);
        header('Location: /seiryokukai_php/public/index.php?page=clients');
        exit;
    }

    if ($action === 'delete' && $id > 0) {
        delete_client($id);
        header('Location: /seiryokukai_php/public/index.php?page=clients');
        exit;
    }

    if ($action === 'add' && $name !== '') {
        add_client($name);
    }

    header('Location: /seiryokukai_php/public/index.php?page=clients');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(read_clients(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
