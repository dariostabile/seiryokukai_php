<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\AutenticazioneService;

function aut_service(): AutenticazioneService
{
    static $service = null;

    if (!$service instanceof AutenticazioneService) {
        $service = new AutenticazioneService();
    }

    return $service;
}
