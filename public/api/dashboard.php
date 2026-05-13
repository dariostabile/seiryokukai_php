<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'user' => current_user(),
    'stats' => dashboard_stats(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
