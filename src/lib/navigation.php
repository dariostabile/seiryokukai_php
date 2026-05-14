<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\NavigationService;

function navigation_service(): NavigationService
{
    static $service = null;

    if (!$service instanceof NavigationService) {
        $service = new NavigationService();
    }

    return $service;
}
