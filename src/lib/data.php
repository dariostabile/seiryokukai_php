<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\DatiService;

function dati_service(): DatiService
{
    static $service = null;

    if (!$service instanceof DatiService) {
        $service = new DatiService();
    }

    return $service;
}
