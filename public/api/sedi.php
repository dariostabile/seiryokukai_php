<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\Sedi\AddSedeRequest;
use App\Requests\Sedi\UpdateSedeRequest;
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

/**
 * @param array<string, mixed> $post
 * @return array<string, string>
 */
function build_add_context_from_post(array $post): array
{
    $context = [];

    foreach ($post as $key => $value) {
        if (!is_string($key)) {
            continue;
        }

        if (in_array($key, ['action', 'ajax', 'page', 'open_add', 'open_edit', 'edit_id', 'id'], true)) {
            continue;
        }

        if (is_scalar($value) || $value === null) {
            $context['add_' . $key] = (string) $value;
        }
    }

    return $context;
}

$auth = aut_service();
$sedi = sedi_service();
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
            $sedi->deleteSede($id);
        }

        if ($wantsJson) {
            json_success('Sede eliminata con successo');
        }

        header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20eliminata%20con%20successo');
        exit;
    }

    if ($action === 'update') {
        try {
            $request = new UpdateSedeRequest($_POST);
            $sedi->updateSede(
                $request->getInt('id'),
                $request->getString('name'),
                $request->getString('code'),
                $request->getInt('active', 1)
            );

            if ($wantsJson) {
                json_success('Sede modificata con successo', [
                    'id' => $request->getInt('id'),
                ]);
            }

            header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20modificata%20con%20successo');
            exit;
        } catch (ValidationException $e) {
            if ($wantsJson) {
                handle_validation_errors_json($e->errors(), 422);
            }

            $validationErrors = $e->errors();
            handle_validation_errors(
                $validationErrors,
                'sedi',
                [
                    'err' => reset($validationErrors) ?: 'Errore di validazione',
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_name' => (string) ($_POST['name'] ?? ''),
                    'edit_code' => (string) ($_POST['code'] ?? ''),
                    'edit_active' => (string) ($_POST['active'] ?? '1'),
                ]
            );
        } catch (\InvalidArgumentException $e) {
            if ($wantsJson) {
                handle_validation_errors_json(['name' => $e->getMessage()], 422);
            }

            handle_validation_errors(
                ['name' => $e->getMessage()],
                'sedi',
                [
                    'err' => $e->getMessage(),
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_name' => (string) ($_POST['name'] ?? ''),
                    'edit_code' => (string) ($_POST['code'] ?? ''),
                    'edit_active' => (string) ($_POST['active'] ?? '1'),
                ]
            );
        }
    }

    try {
        $request = new AddSedeRequest($_POST);
        $newSede = $sedi->addSede(
            $request->getString('name'),
            $request->getString('code'),
            $request->getInt('active', 1)
        );

        if ($wantsJson) {
            json_success('Sede creata con successo', [
                'id' => (int) ($newSede['id'] ?? 0),
                'name' => (string) ($newSede['name'] ?? ''),
            ]);
        }

        header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20creata%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        if ($wantsJson) {
            handle_validation_errors_json($e->errors(), 422);
        }

        $addContext = build_add_context_from_post($_POST);
        handle_validation_errors(
            $e->errors(),
            'sedi',
            $addContext
        );
    } catch (\InvalidArgumentException $e) {
        if ($wantsJson) {
            handle_validation_errors_json(['name' => $e->getMessage()], 422);
        }

        $addContext = build_add_context_from_post($_POST);
        handle_validation_errors(
            ['name' => $e->getMessage()],
            'sedi',
            $addContext
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
    $activeOnly = ((int) ($_GET['active_only'] ?? 0)) === 1;

    $page = $sedi->readSediPage($start, $length, $search, $orderColumn, $orderDir, $activeOnly);

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
echo json_encode($sedi->readSedi(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
