<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use App\Requests\Atleti\AddIscrizioneAtletaRequest;
use App\Requests\ValidationException;

$validPayload = [
    'idatleta' => 1,
    'data_inizio_iscrizione' => '2026-06-01',
    'data_fine_iscrizione' => '2026-07-01',
    'data_iscrizione_corso' => '2026-06-01',
    'abbonamento' => 1,
    'totale_abbonamento' => '49.90',
    'stato_iscrizione' => 'A',
    'course_ids' => [1, '2', '2'],
    'note_iscrizione' => 'smoke',
];

$invalidPayload = [
    'idatleta' => 1,
    'data_inizio_iscrizione' => '2026-06-10',
    'data_fine_iscrizione' => '2026-06-01',
    'abbonamento' => 5,
    'stato_iscrizione' => 'A',
    'course_ids' => [],
];

try {
    $request = new AddIscrizioneAtletaRequest($validPayload);
    $courseIds = $request->getArray('course_ids');
    echo 'valid_payload=OK course_ids=' . implode(',', $courseIds) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'valid_payload=ERROR ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

try {
    new AddIscrizioneAtletaRequest($invalidPayload);
    fwrite(STDERR, 'invalid_payload=ERROR expected validation failure' . PHP_EOL);
    exit(1);
} catch (ValidationException $e) {
    echo 'invalid_payload=OK errors=' . count($e->errors()) . PHP_EOL;
}
