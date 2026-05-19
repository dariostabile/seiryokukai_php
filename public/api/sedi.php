<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\AddSiteRequest;
use App\Requests\UpdateSiteRequest;
use App\Requests\ValidationException;

$auth = aut_service();
$sedi = sedi_service();

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
                $sedi->deleteSite($id);
            }
            header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20eliminata%20con%20successo');
            exit;
        }

        if ($action === 'update') {
            $request = new UpdateSiteRequest($_POST);
            $sedi->updateSite(
                $request->getInt('id'),
                $request->getString('name'),
                $request->getString('code'),
                $request->getInt('active', 1)
            );
            header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20modificata%20con%20successo');
            exit;
        }

        // Default: add
        $request = new AddSiteRequest($_POST);
        $sedi->addSite(
            $request->getString('name'),
            $request->getString('code'),
            $request->getInt('active', 1)
        );
        header('Location: /seiryokukai_php/public/index.php?page=sedi&ok=Sede%20creata%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        handle_validation_errors(
            $e->errors(),
            'sedi',
            [
                'add_name' => $_POST['name'] ?? '',
                'add_code' => $_POST['code'] ?? '',
                'add_active' => $_POST['active'] ?? '1',
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
    $activeOnly = ((int) ($_GET['active_only'] ?? 0)) === 1;

    $page = $sedi->readSitesPage($start, $length, $search, $orderColumn, $orderDir, $activeOnly);

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
echo json_encode($sedi->readSites(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
