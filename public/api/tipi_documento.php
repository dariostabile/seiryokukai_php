<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\TipiDocumenti\AddTipoDocumentoRequest;
use App\Requests\TipiDocumenti\UpdateTipoDocumentoRequest;
use App\Requests\ValidationException;

function wants_json_response(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';
}

function json_success(string $message, array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'type' => 'ok',
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$auth = aut_service();
$tipiDocumento = tipi_documento_service();
$wantsJson = wants_json_response();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'add'));

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $tipiDocumento->deleteTipoDocumento($id);
        }

        if ($wantsJson) {
            json_success('Tipo documento eliminato con successo');
        }

        header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20eliminato%20con%20successo');
        exit;
    }

    if ($action === 'update') {
        try {
            $request = new UpdateTipoDocumentoRequest($_POST);
            $tipiDocumento->updateTipoDocumento(
                $request->getInt('id'),
                $request->getString('type')
            );

            if ($wantsJson) {
                json_success('Tipo documento modificato con successo', [
                    'id' => $request->getInt('id'),
                ]);
            }

            header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20modificato%20con%20successo');
            exit;
        } catch (ValidationException $e) {
            if ($wantsJson) {
                handle_validation_errors_json($e->errors(), 422);
            }

            $validationErrors = $e->errors();
            handle_validation_errors(
                $validationErrors,
                'tipi_documento',
                [
                    'err' => reset($validationErrors) ?: 'Errore di validazione',
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_type' => (string) ($_POST['type'] ?? ''),
                ]
            );
        } catch (\InvalidArgumentException $e) {
            if ($wantsJson) {
                handle_validation_errors_json(['type' => $e->getMessage()], 422);
            }

            handle_validation_errors(
                ['type' => $e->getMessage()],
                'tipi_documento',
                [
                    'err' => $e->getMessage(),
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_type' => (string) ($_POST['type'] ?? ''),
                ]
            );
        }
    }

    try {
        $request = new AddTipoDocumentoRequest($_POST);
        $newTipoDocumento = $tipiDocumento->addTipoDocumento(
            $request->getString('type')
        );

        if ($wantsJson) {
            json_success('Tipo documento creato con successo', [
                'id' => (int) ($newTipoDocumento['id'] ?? 0),
                'type' => (string) ($newTipoDocumento['type'] ?? ''),
            ]);
        }

        header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20creato%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        if ($wantsJson) {
            handle_validation_errors_json($e->errors(), 422);
        }

        handle_validation_errors(
            $e->errors(),
            'tipi_documento',
            [
                'add_type' => $_POST['type'] ?? '',
            ]
        );
    } catch (\InvalidArgumentException $e) {
        if ($wantsJson) {
            handle_validation_errors_json(['type' => $e->getMessage()], 422);
        }

        handle_validation_errors(
            ['type' => $e->getMessage()],
            'tipi_documento',
            [
                'err' => $e->getMessage(),
                'add_type' => $_POST['type'] ?? '',
            ]
        );
    }
}

if (isset($_GET['draw'])) {
    $draw = (int) ($_GET['draw'] ?? 0);
    $start = (int) ($_GET['start'] ?? 0);
    $length = (int) ($_GET['length'] ?? 10);
    $search = trim((string) ($_GET['search']['value'] ?? ''));

    $orderColumnIndex = (int) ($_GET['order'][0]['column'] ?? 0);
    $orderDir = (string) ($_GET['order'][0]['dir'] ?? 'desc');
    $orderColumn = (string) ($_GET['columns'][$orderColumnIndex]['data'] ?? 'id');

    $page = $tipiDocumento->readTipiDocumentiPage($start, $length, $search, $orderColumn, $orderDir);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int) ($page['total'] ?? 0),
        'recordsFiltered' => (int) ($page['filtered'] ?? 0),
        'data' => $page['data'] ?? [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($tipiDocumento->readTipiDocumenti(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
