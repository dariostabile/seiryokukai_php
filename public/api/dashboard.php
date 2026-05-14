<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

$auth = auth_service();
$data = data_service();

header('Content-Type: application/json; charset=utf-8');

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'user' => $auth->currentUser(),
    'stats' => $data->dashboardStats(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
