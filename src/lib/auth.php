<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\AuthService;

function auth_service(): AuthService
{
    static $service = null;

    if (!$service instanceof AuthService) {
        $service = new AuthService();
    }

    return $service;
}
