<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function attempt_login(string $username, string $password): bool
{
    $allowedUser = env_value('APP_USER', 'admin');
    $allowedPass = env_value('APP_PASS', 'admin123');

    if ($username !== $allowedUser || $password !== $allowedPass) {
        return false;
    }

    $_SESSION['user'] = [
        'username' => $username,
        'name' => 'Admin Seiryokukai',
        'role' => 'Amministratore',
    ];

    return true;
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}
