<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\AddDocumentTypeRequest;
use App\Requests\UpdateDocumentTypeRequest;
use App\Requests\ValidationException;

$auth = aut_service();
$tipiDocumento = tipi_documento_service();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'add'));

    try {
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $tipiDocumento->deleteDocumentType($id);
        }
            header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20eliminato%20con%20successo');
        exit;
    }

    if ($action === 'update') {
            $request = new UpdateDocumentTypeRequest($_POST);
            $tipiDocumento->updateDocumentType(
                $request->getInt('id'),
                $request->getString('type')
            );
            header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20modificato%20con%20successo');
        exit;
    }

        // Default: add
        $request = new AddDocumentTypeRequest($_POST);
        $tipiDocumento->addDocumentType(
            $request->getString('type')
        );
        header('Location: /seiryokukai_php/public/index.php?page=tipi_documento&ok=Tipo%20documento%20creato%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        handle_validation_errors(
            $e->errors(),
            'tipi_documento',
            [
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

    $page = $tipiDocumento->readDocumentTypesPage($start, $length, $search, $orderColumn, $orderDir);

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
echo json_encode($tipiDocumento->readDocumentTypes(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
