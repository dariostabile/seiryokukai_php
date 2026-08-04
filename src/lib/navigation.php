<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\NavigazioneService;

function navigazione_service(): NavigazioneService
{
    static $service = null;

    if (!$service instanceof NavigazioneService) {
        $service = new NavigazioneService();
    }

    return $service;
}
