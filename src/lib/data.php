<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\DataService;

function data_service(): DataService
{
    static $service = null;

    if (!$service instanceof DataService) {
        $service = new DataService();
    }

    return $service;
}
