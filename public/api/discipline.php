<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\Discipline\AddDisciplinaRequest;
use App\Requests\Discipline\UpdateDisciplinaRequest;
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
$disciplina = disciplina_service();
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
            $disciplina->deleteDisciplina($id);
        }

        if ($wantsJson) {
            json_success('Disciplina eliminata con successo');
        }

        header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20eliminata%20con%20successo');
        exit;
    }

    if ($action === 'update') {
        try {
            $request = new UpdateDisciplinaRequest($_POST);
            $disciplina->updateDisciplina(
                $request->getInt('id'),
                $request->getString('name'),
                $request->getString('notes')
            );

            if ($wantsJson) {
                json_success('Disciplina modificata con successo', [
                    'id' => $request->getInt('id'),
                ]);
            }

            header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20modificata%20con%20successo');
            exit;
        } catch (ValidationException $e) {
            if ($wantsJson) {
                handle_validation_errors_json($e->errors(), 422);
            }

            $validationErrors = $e->errors();
            handle_validation_errors(
                $validationErrors,
                'disciplina',
                [
                    'err' => reset($validationErrors) ?: 'Errore di validazione',
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_name' => $_POST['name'] ?? '',
                    'edit_notes' => $_POST['notes'] ?? '',
                ]
            );
        } catch (\InvalidArgumentException $e) {
            if ($wantsJson) {
                handle_validation_errors_json(['name' => $e->getMessage()], 422);
            }

            handle_validation_errors(
                ['name' => $e->getMessage()],
                'disciplina',
                [
                    'err' => $e->getMessage(),
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['id'] ?? ''),
                    'edit_name' => $_POST['name'] ?? '',
                    'edit_notes' => $_POST['notes'] ?? '',
                ]
            );
        }
    }

    try {
        $request = new AddDisciplinaRequest($_POST);
        $newDisciplina = $disciplina->addDisciplina(
            $request->getString('name'),
            $request->getString('notes')
        );

        if ($wantsJson) {
            json_success('Disciplina creata con successo', [
                'id' => (int) ($newDisciplina['id'] ?? 0),
                'name' => (string) ($newDisciplina['name'] ?? ''),
            ]);
        }

        header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20creata%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        if ($wantsJson) {
            handle_validation_errors_json($e->errors(), 422);
        }

        handle_validation_errors(
            $e->errors(),
            'disciplina',
            [
                'add_name' => $_POST['name'] ?? '',
                'add_notes' => $_POST['notes'] ?? '',
            ]
        );
    } catch (\InvalidArgumentException $e) {
        if ($wantsJson) {
            handle_validation_errors_json(['name' => $e->getMessage()], 422);
        }

        handle_validation_errors(
            ['name' => $e->getMessage()],
            'disciplina',
            [
                'err' => $e->getMessage(),
                'add_name' => $_POST['name'] ?? '',
                'add_notes' => $_POST['notes'] ?? '',
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

    $page = $disciplina->readDisciplinePage($start, $length, $search, $orderColumn, $orderDir);

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
echo json_encode($disciplina->readDiscipline(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);