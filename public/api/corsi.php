<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\Corsi\AddCorsoRequest;
use App\Requests\Corsi\UpdateCorsoRequest;
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

        if (in_array($key, ['action', 'ajax', 'page', 'open_add', 'open_edit', 'edit_id', 'corso_id'], true)) {
            continue;
        }

        $contextKey = 'add_' . $key;

        if (is_scalar($value) || $value === null) {
            $context[$contextKey] = (string) $value;
            continue;
        }

        if (is_array($value)) {
            $serialized = array_map(static fn ($item): string => is_scalar($item) || $item === null ? (string) $item : '', $value);
            $serialized = array_values(array_filter($serialized, static fn (string $item): bool => $item !== ''));
            $context[$contextKey] = implode(',', $serialized);
        }
    }

    return $context;
}

$auth = aut_service();
$corsi = corsi_service();
$wantsJson = wants_json_response();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? 'add'));


    $orari = [
        'lun_inizio' => trim((string) ($_POST['lun_inizio'] ?? '')),
        'lun_fine' => trim((string) ($_POST['lun_fine'] ?? '')),
        'mar_inizio' => trim((string) ($_POST['mar_inizio'] ?? '')),
        'mar_fine' => trim((string) ($_POST['mar_fine'] ?? '')),
        'mer_inizio' => trim((string) ($_POST['mer_inizio'] ?? '')),
        'mer_fine' => trim((string) ($_POST['mer_fine'] ?? '')),
        'gio_inizio' => trim((string) ($_POST['gio_inizio'] ?? '')),
        'gio_fine' => trim((string) ($_POST['gio_fine'] ?? '')),
        'ven_inizio' => trim((string) ($_POST['ven_inizio'] ?? '')),
        'ven_fine' => trim((string) ($_POST['ven_fine'] ?? '')),
        'sab_inizio' => trim((string) ($_POST['sab_inizio'] ?? '')),
        'sab_fine' => trim((string) ($_POST['sab_fine'] ?? '')),
        'dom_inizio' => trim((string) ($_POST['dom_inizio'] ?? '')),
        'dom_fine' => trim((string) ($_POST['dom_fine'] ?? '')),
    ];

    if ($action === 'delete') {
        $corsoId = (int) ($_POST['corso_id'] ?? 0);
        if ($corsoId > 0) {
            $corsi->deleteCorso($corsoId);
        }

        if ($wantsJson) {
            json_success('Corso eliminato con successo');
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi&ok=Corso%20eliminato%20con%20successo');
        exit;
    }

    if ($action === 'update') {
        try {
            $request = new UpdateCorsoRequest($_POST);
            $corsi->updateCorso(
                $request->getInt('corso_id'),
                $request->getInt('sede_id'),
                $request->getInt('disciplina_id'),
                $request->getInt('user_id'),
                $request->getString('name'),
                $request->getString('start_date') !== '' ? $request->getString('start_date') : null,
                $request->getString('end_date') !== '' ? $request->getString('end_date') : null,
                $request->getString('monthly_fee') !== '' ? (float) $request->getString('monthly_fee') : null,
                $request->getInt('active', 1),
                $orari
            );

            if ($wantsJson) {
                json_success('Corso modificato con successo', [
                    'id' => $request->getInt('corso_id'),
                ]);
            }

            header('Location: /seiryokukai_php/public/index.php?page=corsi&ok=Corso%20modificato%20con%20successo');
            exit;
        } catch (ValidationException $e) {
            if ($wantsJson) {
                handle_validation_errors_json($e->errors(), 422);
            }

            handle_validation_errors(
                $e->errors(),
                'corsi',
                [
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['corso_id'] ?? ''),
                    'edit_name' => (string) ($_POST['name'] ?? ''),
                    'edit_sede_id' => (string) ($_POST['sede_id'] ?? ''),
                    'edit_disciplina_id' => (string) ($_POST['disciplina_id'] ?? ''),
                    'edit_user_id' => (string) ($_POST['user_id'] ?? ''),
                    'edit_start_date' => (string) ($_POST['start_date'] ?? ''),
                    'edit_end_date' => (string) ($_POST['end_date'] ?? ''),
                    'edit_monthly_fee' => (string) ($_POST['monthly_fee'] ?? ''),
                    'edit_active' => (string) ($_POST['active'] ?? '1'),
                    'edit_lun_inizio' => (string) ($_POST['lun_inizio'] ?? ''),
                    'edit_lun_fine' => (string) ($_POST['lun_fine'] ?? ''),
                    'edit_mar_inizio' => (string) ($_POST['mar_inizio'] ?? ''),
                    'edit_mar_fine' => (string) ($_POST['mar_fine'] ?? ''),
                    'edit_mer_inizio' => (string) ($_POST['mer_inizio'] ?? ''),
                    'edit_mer_fine' => (string) ($_POST['mer_fine'] ?? ''),
                    'edit_gio_inizio' => (string) ($_POST['gio_inizio'] ?? ''),
                    'edit_gio_fine' => (string) ($_POST['gio_fine'] ?? ''),
                    'edit_ven_inizio' => (string) ($_POST['ven_inizio'] ?? ''),
                    'edit_ven_fine' => (string) ($_POST['ven_fine'] ?? ''),
                    'edit_sab_inizio' => (string) ($_POST['sab_inizio'] ?? ''),
                    'edit_sab_fine' => (string) ($_POST['sab_fine'] ?? ''),
                    'edit_dom_inizio' => (string) ($_POST['dom_inizio'] ?? ''),
                    'edit_dom_fine' => (string) ($_POST['dom_fine'] ?? ''),
                ]
            );
        } catch (\InvalidArgumentException $e) {
            if ($wantsJson) {
                handle_validation_errors_json(['user_id' => $e->getMessage()], 422);
            }

            handle_validation_errors(
                ['user_id' => $e->getMessage()],
                'corsi',
                [
                    'err' => $e->getMessage(),
                    'open_edit' => '1',
                    'edit_id' => (string) ($_POST['corso_id'] ?? ''),
                    'edit_name' => (string) ($_POST['name'] ?? ''),
                    'edit_sede_id' => (string) ($_POST['sede_id'] ?? ''),
                    'edit_disciplina_id' => (string) ($_POST['disciplina_id'] ?? ''),
                    'edit_user_id' => (string) ($_POST['user_id'] ?? ''),
                    'edit_start_date' => (string) ($_POST['start_date'] ?? ''),
                    'edit_end_date' => (string) ($_POST['end_date'] ?? ''),
                    'edit_monthly_fee' => (string) ($_POST['monthly_fee'] ?? ''),
                    'edit_active' => (string) ($_POST['active'] ?? '1'),
                    'edit_lun_inizio' => (string) ($_POST['lun_inizio'] ?? ''),
                    'edit_lun_fine' => (string) ($_POST['lun_fine'] ?? ''),
                    'edit_mar_inizio' => (string) ($_POST['mar_inizio'] ?? ''),
                    'edit_mar_fine' => (string) ($_POST['mar_fine'] ?? ''),
                    'edit_mer_inizio' => (string) ($_POST['mer_inizio'] ?? ''),
                    'edit_mer_fine' => (string) ($_POST['mer_fine'] ?? ''),
                    'edit_gio_inizio' => (string) ($_POST['gio_inizio'] ?? ''),
                    'edit_gio_fine' => (string) ($_POST['gio_fine'] ?? ''),
                    'edit_ven_inizio' => (string) ($_POST['ven_inizio'] ?? ''),
                    'edit_ven_fine' => (string) ($_POST['ven_fine'] ?? ''),
                    'edit_sab_inizio' => (string) ($_POST['sab_inizio'] ?? ''),
                    'edit_sab_fine' => (string) ($_POST['sab_fine'] ?? ''),
                    'edit_dom_inizio' => (string) ($_POST['dom_inizio'] ?? ''),
                    'edit_dom_fine' => (string) ($_POST['dom_fine'] ?? ''),
                ]
            );
        }
    }

    try {
        // Default: add
        $request = new AddCorsoRequest($_POST);
        $nuovoCorso = $corsi->addCorso(
            $request->getInt('sede_id'),
            $request->getInt('disciplina_id'),
            $request->getInt('user_id'),
            $request->getString('name'),
            $request->getString('start_date') !== '' ? $request->getString('start_date') : null,
            $request->getString('end_date') !== '' ? $request->getString('end_date') : null,
            $request->getString('monthly_fee') !== '' ? (float) $request->getString('monthly_fee') : null,
            $request->getInt('active', 1),
            $orari
        );

        if ($wantsJson) {
            json_success('Corso creato con successo', [
                'id' => (int) ($nuovoCorso['id'] ?? 0),
                'name' => (string) ($nuovoCorso['name'] ?? ''),
            ]);
        }

        header('Location: /seiryokukai_php/public/index.php?page=corsi&ok=Corso%20creato%20con%20successo');
        exit;
    } catch (ValidationException $e) {
        if ($wantsJson) {
            handle_validation_errors_json($e->errors(), 422);
        }

        $addContext = build_add_context_from_post($_POST);
        handle_validation_errors(
            $e->errors(),
            'corsi',
            $addContext
        );
    } catch (\InvalidArgumentException $e) {
        if ($wantsJson) {
            handle_validation_errors_json(['user_id' => $e->getMessage()], 422);
        }

        $addContext = build_add_context_from_post($_POST);
        handle_validation_errors(
            ['user_id' => $e->getMessage()],
            'corsi',
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

    $page = $corsi->readCorsiPage($start, $length, $search, $orderColumn, $orderDir, $activeOnly);

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
echo json_encode($corsi->readCorsi(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
