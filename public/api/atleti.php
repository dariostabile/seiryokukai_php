<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

$auth = aut_service();
$data = dati_service();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'add'));
    $name = trim((string) ($_POST['name'] ?? ''));
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'status' && $id > 0) {
        $status = trim((string) ($_POST['status'] ?? ''));
        $data->updateClientStatus($id, $status);
        header('Location: /seiryokukai_php/public/index.php?page=atleti');
        exit;
    }

    if ($action === 'delete' && $id > 0) {
        $data->deleteClient($id);
        header('Location: /seiryokukai_php/public/index.php?page=atleti');
        exit;
    }

    if ($action === 'add' && $name !== '') {
        $data->addClient($name);
    }

    header('Location: /seiryokukai_php/public/index.php?page=atleti');
    exit;
}

if (isset($_GET['draw'])) {
    $draw = (int) ($_GET['draw'] ?? 0);
    $start = (int) ($_GET['start'] ?? 0);
    $length = (int) ($_GET['length'] ?? 10);
    $search = trim((string) ($_GET['search']['value'] ?? ''));

    $orderColumnIndex = (int) ($_GET['order'][0]['column'] ?? 0);
    $orderDir = (string) ($_GET['order'][0]['dir'] ?? 'desc');
    $orderColumn = (string) ($_GET['columns'][$orderColumnIndex]['data'] ?? 'id');

    $page = $data->readClientsPage($start, $length, $search, $orderColumn, $orderDir);

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
echo json_encode($data->readClients(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
