<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../autoload.php';

use App\Services\DatiService;
use App\Services\AtletiService;
use App\Services\UtentiService;
use App\Services\SediService;
use App\Services\TipiDocumentoService;
use App\Services\DisciplineService;
use App\Services\CorsiService;
use App\Services\ApplicazioniService;
use App\Services\DashboardService;

// ============ SINGLETONS ============

function dati_service(): DatiService
{
    static $service = null;

    if (!$service instanceof DatiService) {
        $service = new DatiService();
    }

    return $service;
}

function atleti_service(): AtletiService
{
    static $service = null;

    if (!$service instanceof AtletiService) {
        $service = new AtletiService();
    }

    return $service;
}

function utenti_service(): UtentiService
{
    static $service = null;

    if (!$service instanceof UtentiService) {
        $service = new UtentiService();
    }

    return $service;
}

function sedi_service(): SediService
{
    static $service = null;

    if (!$service instanceof SediService) {
        $service = new SediService();
    }

    return $service;
}

function tipi_documento_service(): TipiDocumentoService
{
    static $service = null;

    if (!$service instanceof TipiDocumentoService) {
        $service = new TipiDocumentoService();
    }

    return $service;
}

function disciplina_service(): DisciplineService
{
    static $service = null;

    if (!$service instanceof DisciplineService) {
        $service = new DisciplineService();
    }

    return $service;
}

function corsi_service(): CorsiService
{
    static $service = null;

    if (!$service instanceof CorsiService) {
        $service = new CorsiService();
    }

    return $service;
}

function applicazioni_service(): ApplicazioniService
{
    static $service = null;

    if (!$service instanceof ApplicazioniService) {
        $service = new ApplicazioniService();
    }

    return $service;
}

function dashboard_service(): DashboardService
{
    static $service = null;

    if (!$service instanceof DashboardService) {
        $service = new DashboardService();
    }

    return $service;
}

// ============ VALIDATION HELPERS ============

/**
 * Gestisce gli errori di validazione e reindirizza l'utente.
 *
 * @param array<string, string> $errors - Array di errori dal FormRequest
 * @param string $page - Nome della pagina per il redirect
 * @param array<string, string> $extraParams - Parametri aggiuntivi per il redirect
 */
function handle_validation_errors(array $errors, string $page, array $extraParams = []): void
{
    $firstError = reset($errors) ?: 'Errore di validazione';

    $query = http_build_query(array_merge([
        'page' => $page,
        'err' => $firstError,
    ], $extraParams));

    header('Location: /seiryokukai_php/public/index.php?' . $query);
    exit;
}

/**
 * Gestisce gli errori JSON per le richieste AJAX.
 *
 * @param array<string, string> $errors - Array di errori dal FormRequest
 * @param int $statusCode - Codice HTTP di risposta
 */
function handle_validation_errors_json(array $errors, int $statusCode = 400): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'type' => 'err',
        'message' => reset($errors) ?: 'Errore di validazione',
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
