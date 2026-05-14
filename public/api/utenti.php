<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

$auth = aut_service();
$data = dati_service();
$currentUser = $auth->currentUser();
$currentUserId = (int) ($currentUser['id'] ?? 0);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * @return array{path:string, old_path:string}
 */
function gestisciUploadImmagineUtente(array $file, int $userId, string $oldPath = ''): array
{
    if ($userId <= 0) {
        throw new \InvalidArgumentException('Utente non valido per upload immagine');
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => '', 'old_path' => $oldPath];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new \RuntimeException('Upload immagine non riuscito');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new \RuntimeException('File immagine non valido');
    }

    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        throw new \RuntimeException('L\'immagine deve essere minore di 5MB');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpPath);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        throw new \RuntimeException('Formato immagine non supportato (usa JPG, PNG, WEBP o GIF)');
    }

    $extension = $allowed[$mime];
    $relativeDir = 'public/utenti/' . $userId;
    $absoluteDir = __DIR__ . '/../../' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new \RuntimeException('Impossibile creare la cartella immagini utente');
    }

    $fileName = $userId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = $relativeDir . '/' . $fileName;
    $absolutePath = __DIR__ . '/../../' . $relativePath;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        throw new \RuntimeException('Impossibile salvare l\'immagine utente');
    }

    normalizzaAvatarQuadrato($absolutePath, $mime);

    return ['path' => $relativePath, 'old_path' => $oldPath];
}

/**
 * @return array{path:string, old_path:string}
 */
function gestisciUploadImmagineBase64Utente(string $dataUrl, int $userId, string $oldPath = ''): array
{
    if ($userId <= 0) {
        throw new \InvalidArgumentException('Utente non valido per upload immagine');
    }

    $dataUrl = trim($dataUrl);
    if ($dataUrl === '') {
        return ['path' => '', 'old_path' => $oldPath];
    }

    if (!preg_match('#^data:(image/(?:jpeg|png|webp|gif));base64,([A-Za-z0-9+/=]+)$#', $dataUrl, $matches)) {
        throw new \RuntimeException('Dati immagine ritagliata non validi');
    }

    $mime = (string) ($matches[1] ?? '');
    $base64Data = (string) ($matches[2] ?? '');
    $binary = base64_decode($base64Data, true);
    if ($binary === false || $binary === '') {
        throw new \RuntimeException('Immagine ritagliata non valida');
    }

    if (strlen($binary) > 5 * 1024 * 1024) {
        throw new \RuntimeException('L\'immagine deve essere minore di 5MB');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new \RuntimeException('Formato immagine non supportato');
    }

    $extension = $allowed[$mime];
    $relativeDir = 'public/utenti/' . $userId;
    $absoluteDir = __DIR__ . '/../../' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new \RuntimeException('Impossibile creare la cartella immagini utente');
    }

    $fileName = $userId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = $relativeDir . '/' . $fileName;
    $absolutePath = __DIR__ . '/../../' . $relativePath;

    if (file_put_contents($absolutePath, $binary) === false) {
        throw new \RuntimeException('Impossibile salvare l\'immagine ritagliata');
    }

    normalizzaAvatarQuadrato($absolutePath, $mime);

    return ['path' => $relativePath, 'old_path' => $oldPath];
}

function normalizzaAvatarQuadrato(string $filePath, string $mime): void
{
    if (!is_file($filePath)) {
        return;
    }

    if (!function_exists('imagecreatetruecolor')) {
        return;
    }

    $source = null;
    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagecreatefromjpeg')) {
                $source = @imagecreatefromjpeg($filePath);
            }
            break;
        case 'image/png':
            if (function_exists('imagecreatefrompng')) {
                $source = @imagecreatefrompng($filePath);
            }
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $source = @imagecreatefromwebp($filePath);
            }
            break;
        case 'image/gif':
            if (function_exists('imagecreatefromgif')) {
                $source = @imagecreatefromgif($filePath);
            }
            break;
    }

    if (!$source) {
        return;
    }

    $srcWidth = imagesx($source);
    $srcHeight = imagesy($source);
    if ($srcWidth <= 0 || $srcHeight <= 0) {
        imagedestroy($source);
        return;
    }

    $cropSize = min($srcWidth, $srcHeight);
    $srcX = (int) floor(($srcWidth - $cropSize) / 2);
    $srcY = (int) floor(($srcHeight - $cropSize) / 2);
    $targetSize = 320;

    $dest = imagecreatetruecolor($targetSize, $targetSize);
    if (!$dest) {
        imagedestroy($source);
        return;
    }

    if ($mime === 'image/png' || $mime === 'image/gif' || $mime === 'image/webp') {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent);
    }

    imagecopyresampled(
        $dest,
        $source,
        0,
        0,
        $srcX,
        $srcY,
        $targetSize,
        $targetSize,
        $cropSize,
        $cropSize
    );

    switch ($mime) {
        case 'image/jpeg':
            @imagejpeg($dest, $filePath, 90);
            break;
        case 'image/png':
            @imagepng($dest, $filePath, 6);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                @imagewebp($dest, $filePath, 90);
            }
            break;
        case 'image/gif':
            @imagegif($dest, $filePath);
            break;
    }

    imagedestroy($dest);
    imagedestroy($source);
}

function eliminaImmagineUtente(string $path): void
{
    $cleanPath = ltrim(trim($path), '/');
    if ($cleanPath === '') {
        return;
    }

    if (strpos($cleanPath, 'public/utenti/') !== 0) {
        return;
    }

    $absolutePath = __DIR__ . '/../../' . $cleanPath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }

    $userDir = dirname($absolutePath);
    pulisciCartellaSeVuota($userDir);
}

function pulisciCartellaSeVuota(string $absoluteDir): void
{
    if (!is_dir($absoluteDir)) {
        return;
    }

    $baseUsersDir = realpath(__DIR__ . '/../../public/utenti');
    $realDir = realpath($absoluteDir);
    if ($baseUsersDir === false || $realDir === false) {
        return;
    }

    // Evita cancellazioni fuori dalla cartella immagini utenti.
    if (strpos($realDir, $baseUsersDir . DIRECTORY_SEPARATOR) !== 0) {
        return;
    }

    $entries = @scandir($realDir);
    if (!is_array($entries)) {
        return;
    }

    $visibleEntries = array_values(array_filter($entries, static function (string $entry): bool {
        return $entry !== '.' && $entry !== '..';
    }));

    if (count($visibleEntries) === 0) {
        @rmdir($realDir);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));
    $id = (int) ($_POST['id'] ?? 0);
    $errorContext = [];

    $redirect = static function (string $type, string $message, array $extra = []): void {
        $type = $type === 'err' ? 'err' : 'ok';
        $query = http_build_query(array_merge([
            'page' => 'utenti',
            $type => $message,
        ], $extra));
        header('Location: /seiryokukai_php/public/index.php?' . $query);
        exit;
    };

    try {
        if ($action === 'add') {
            $nome = trim((string) ($_POST['nome'] ?? ''));
            $cognome = trim((string) ($_POST['cognome'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $email = trim((string) ($_POST['email'] ?? ''));
            $profileIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['profile_ids'] ?? [])), static fn (int $id): bool => $id > 0)));
            if ($profileIds === [] && isset($_POST['profile_id'])) {
                $legacyProfileId = (int) ($_POST['profile_id'] ?? 0);
                if ($legacyProfileId > 0) {
                    $profileIds = [$legacyProfileId];
                }
            }
            $status = trim((string) ($_POST['status'] ?? 'Attivo'));
            $attivo = $status !== 'Sospeso';

            $errorContext = [
                'add_nome' => $nome,
                'add_cognome' => $cognome,
                'add_username' => $username,
                'add_email' => $email,
                'add_profile_ids' => implode(',', $profileIds),
                'add_status' => $status,
            ];

            if ($profileIds === []) {
                $redirect('err', 'Seleziona almeno un profilo', $errorContext);
            }

            if ($username !== '' && $password !== '') {
                $data->addUser($nome, $cognome, $username, $password, $email, $profileIds, $attivo);
                $redirect('ok', 'Utente creato con successo');
            }
            $redirect('err', 'Username e password sono obbligatori', $errorContext);
        } elseif ($action === 'update' && $id > 0) {
            $isSelfUpdate = $id === $currentUserId;
            $existingUser = $data->findUserById($id);
            if (!is_array($existingUser)) {
                $redirect('err', 'Utente non trovato');
            }

            $nome = trim((string) ($_POST['nome'] ?? ''));
            $cognome = trim((string) ($_POST['cognome'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $newPassword = (string) ($_POST['password'] ?? '');
            $profileIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['profile_ids'] ?? [])), static fn (int $id): bool => $id > 0)));
            if ($profileIds === [] && isset($_POST['profile_id'])) {
                $legacyProfileId = (int) ($_POST['profile_id'] ?? 0);
                if ($legacyProfileId > 0) {
                    $profileIds = [$legacyProfileId];
                }
            }
            $status = trim((string) ($_POST['status'] ?? 'Attivo'));
            $applicationIds = array_values(array_unique(array_map('intval', (array) ($_POST['application_ids'] ?? []))));

            // Sul proprio account da questa sezione sono consentiti solo dati anagrafici base.
            if ($isSelfUpdate) {
                $username = trim((string) ($existingUser['username'] ?? ''));
                $profileIds = array_values(array_unique(array_map('intval', (array) ($existingUser['profile_ids'] ?? []))));
                $status = trim((string) ($existingUser['status'] ?? 'Attivo'));
                $applicationIds = array_values(array_unique(array_map('intval', (array) ($existingUser['application_ids'] ?? []))));
            }

            $attivo = $status !== 'Sospeso';

            $errorContext = [
                'open_edit' => 1,
                'edit_id' => $id,
                'edit_nome' => $nome,
                'edit_cognome' => $cognome,
                'edit_username' => $username,
                'edit_email' => $email,
                'edit_profile_ids' => implode(',', $profileIds),
                'edit_status' => $status,
                'edit_application_ids' => implode(',', $applicationIds),
                'edit_image' => trim((string) ($_POST['current_image_path'] ?? '')),
            ];

            if (!$isSelfUpdate && $profileIds === []) {
                $redirect('err', 'Seleziona almeno un profilo', $errorContext);
            }

                if ($username !== '') {
                    $currentImagePath = trim((string) ($existingUser['image_path'] ?? ($_POST['current_image_path'] ?? '')));
                $removeImage = ((string) ($_POST['remove_image'] ?? '0')) === '1';
                $cropImageBase64 = trim((string) ($_POST['crop_image_base64'] ?? ''));
                $newImagePath = $currentImagePath;

                if ($removeImage) {
                    $newImagePath = '';
                }

                if ($cropImageBase64 !== '') {
                    $upload = gestisciUploadImmagineBase64Utente($cropImageBase64, $id, $currentImagePath);
                    if ($upload['path'] !== '') {
                        $newImagePath = $upload['path'];
                    }
                } elseif (isset($_FILES['image']) && is_array($_FILES['image'])) {
                    $upload = gestisciUploadImmagineUtente($_FILES['image'], $id, $currentImagePath);
                    if ($upload['path'] !== '') {
                        $newImagePath = $upload['path'];
                    }
                }

                  $data->updateUser(
                      $id,
                      $nome,
                      $cognome,
                      $username,
                      $email,
                                            $profileIds,
                      $attivo,
                      $newImagePath !== '' ? $newImagePath : null,
                        $newPassword,
                        $applicationIds
                  );

                if ($currentImagePath !== '' && $currentImagePath !== $newImagePath) {
                    eliminaImmagineUtente($currentImagePath);
                }

                $redirect('ok', 'Utente aggiornato con successo');
            }
            $redirect('err', 'Username obbligatorio', $errorContext);
          } elseif ($action === 'status' && $id > 0) {
            if ($id === $currentUserId) {
                $redirect('err', 'Non puoi cambiare lo stato del tuo utente da questa sezione');
            }

            $status = trim((string) ($_POST['status'] ?? ''));
            $data->updateUserStatus($id, $status);
            $redirect('ok', 'Stato utente aggiornato');
        } elseif ($action === 'delete' && $id > 0) {
            if ($id === $currentUserId) {
                $redirect('err', 'Non puoi eliminare il tuo utente');
            }

            $data->deleteUser($id);
            $redirect('ok', 'Utente eliminato');
        }

        $redirect('err', 'Operazione non valida');
    } catch (\Throwable $e) {
        $redirect('err', $e->getMessage(), $errorContext);
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

    $page = $data->readUsersPage($start, $length, $search, $orderColumn, $orderDir);

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
echo json_encode($data->readUsers(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
