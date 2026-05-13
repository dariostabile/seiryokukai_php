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
    $type = trim((string) ($_POST['type'] ?? ''));

    if ($type !== '') {
        add_document_type($type);
    }

    header('Location: /seiryokukai_php/public/index.php?page=document_types');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(read_document_types(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
