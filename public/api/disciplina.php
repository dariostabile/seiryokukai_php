<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\AddDisciplineRequest;
use App\Requests\UpdateDisciplineRequest;
use App\Requests\ValidationException;

$auth = aut_service();
$discipline = discipline_service();

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
            $discipline->deleteDiscipline($id);
        }
        header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20eliminata%20con%20successo');
        exit;
    }

    if ($action === 'update') {
        try {
            $request = new UpdateDisciplineRequest($_POST);
            $discipline->updateDiscipline(
                $request->getInt('id'),
                $request->getString('name'),
                $request->getString('notes')
            );
            header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20modificata%20con%20successo');
            exit;
        } catch (ValidationException $e) {
            handle_validation_errors(
                $e->errors(),
                'disciplina',
                [
                    'add_name' => $_POST['name'] ?? '',
                    'add_notes' => $_POST['notes'] ?? '',
                ]
            );
        }
    }

    try {
        $request = new AddDisciplineRequest($_POST);
        $discipline->addDiscipline(
            $request->getString('name'),
            $request->getString('notes')
        );
        header('Location: /seiryokukai_php/public/index.php?page=disciplina&ok=Disciplina%20creata%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        handle_validation_errors(
            $e->errors(),
            'disciplina',
            [
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

    $page = $discipline->readDisciplinesPage($start, $length, $search, $orderColumn, $orderDir);

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
echo json_encode($discipline->readDisciplines(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
