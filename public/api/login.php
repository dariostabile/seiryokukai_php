<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';

$auth = aut_service();

$username = trim((string) ($_POST['username'] ?? ''));
$password = trim((string) ($_POST['password'] ?? ''));

if (!$auth->attemptLogin($username, $password)) {
    header('Location: /seiryokukai_php/public/index.php?page=login');
    exit;
}

header('Location: /seiryokukai_php/public/index.php?page=dashboard');
exit;
