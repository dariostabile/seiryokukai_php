<?php

declare(strict_types=1);

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../src/autoload.php';

$service = new App\Services\AtletiService();
$athleteId = 3;

try {
    $athlete = $service->findAtletaById($athleteId);

    if ($athlete === null) {
        echo 'Athlete #' . $athleteId . ' not found' . PHP_EOL;
        exit(0);
    }

    $iscrizioniCount = isset($athlete['iscrizioni']) && is_array($athlete['iscrizioni'])
        ? count($athlete['iscrizioni'])
        : 0;
    $pagamentiCount = isset($athlete['pagamenti']) && is_array($athlete['pagamenti'])
        ? count($athlete['pagamenti'])
        : 0;

    echo 'OK findAtletaById(' . $athleteId . ')' . PHP_EOL;
    echo 'iscrizioni=' . $iscrizioniCount . PHP_EOL;
    echo 'pagamenti=' . $pagamentiCount . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
