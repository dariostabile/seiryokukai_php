<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/data.php';

use App\Requests\Atleti\AddAtletaRequest;
use App\Requests\Atleti\AddDocumentoAtletaRequest;
use App\Requests\Atleti\AddIscrizioneAtletaRequest;
use App\Requests\Atleti\AddPagamentoAtletaRequest;
use App\Requests\Atleti\UpdateAtletaRequest;
use App\Requests\ValidationException;

$auth = aut_service();
$atleti = atleti_service();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * @param array<string, scalar|null> $params
 */
function redirect_atleti(array $params = []): void
{
    $query = http_build_query(array_merge([
        'page' => 'atleti',
    ], $params));

    header('Location: /seiryokukai_php/public/index.php?' . $query);
    exit;
}

function gestisciUploadDocumentoAtleta(array $file, int $athleteId): string
{
    if ($athleteId <= 0) {
        throw new \InvalidArgumentException('Atleta non valido per upload documento');
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new \RuntimeException('Upload documento non riuscito');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new \RuntimeException('File documento non valido');
    }

    if ($size <= 0 || $size > 8 * 1024 * 1024) {
        throw new \RuntimeException('Il documento deve essere minore di 8MB');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpPath);
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new \RuntimeException('Formato documento non supportato (usa PDF, JPG, PNG o WEBP)');
    }

    $extension = $allowed[$mime];
    $relativeDir = 'public/atleti/' . $athleteId . '/documenti';
    $absoluteDir = __DIR__ . '/../../' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new \RuntimeException('Impossibile creare la cartella documenti atleta');
    }

    $originalName = trim((string) ($file['name'] ?? 'documento'));
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName ?? '') ?: 'documento';
    $fileName = $athleteId . '_' . date('YmdHis') . '_' . $baseName . '.' . $extension;
    $relativePath = $relativeDir . '/' . $fileName;
    $absolutePath = __DIR__ . '/../../' . $relativePath;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        throw new \RuntimeException('Impossibile salvare il documento atleta');
    }

    return $relativePath;
}

function gestisciUploadImmagineAtleta(array $file, int $athleteId): string
{
    if ($athleteId <= 0) {
        throw new \InvalidArgumentException('Atleta non valido per upload immagine');
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
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
    $relativeDir = 'public/atleti/' . $athleteId;
    $absoluteDir = __DIR__ . '/../../' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new \RuntimeException('Impossibile creare la cartella immagini atleta');
    }

    $fileName = $athleteId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = $relativeDir . '/' . $fileName;
    $absolutePath = __DIR__ . '/../../' . $relativePath;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        throw new \RuntimeException('Impossibile salvare l\'immagine atleta');
    }

    normalizzaAvatarQuadratoAtleta($absolutePath, $mime);

    return $relativePath;
}

function gestisciUploadImmagineBase64Atleta(string $dataUrl, int $athleteId): string
{
    if ($athleteId <= 0) {
        throw new \InvalidArgumentException('Atleta non valido per upload immagine');
    }

    $dataUrl = trim($dataUrl);
    if ($dataUrl === '') {
        return '';
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
    $relativeDir = 'public/atleti/' . $athleteId;
    $absoluteDir = __DIR__ . '/../../' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new \RuntimeException('Impossibile creare la cartella immagini atleta');
    }

    $fileName = $athleteId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = $relativeDir . '/' . $fileName;
    $absolutePath = __DIR__ . '/../../' . $relativePath;

    if (file_put_contents($absolutePath, $binary) === false) {
        throw new \RuntimeException('Impossibile salvare l\'immagine ritagliata');
    }

    normalizzaAvatarQuadratoAtleta($absolutePath, $mime);

    return $relativePath;
}

function normalizzaAvatarQuadratoAtleta(string $filePath, string $mime): void
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
    $destinationSize = 512;

    $destination = imagecreatetruecolor($destinationSize, $destinationSize);
    if (!$destination) {
        imagedestroy($source);
        return;
    }

    if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
        imagefilledrectangle($destination, 0, 0, $destinationSize, $destinationSize, $transparent);
    }

    imagecopyresampled(
        $destination,
        $source,
        0,
        0,
        $srcX,
        $srcY,
        $destinationSize,
        $destinationSize,
        $cropSize,
        $cropSize
    );

    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagejpeg')) {
                imagejpeg($destination, $filePath, 90);
            }
            break;
        case 'image/png':
            if (function_exists('imagepng')) {
                imagepng($destination, $filePath, 6);
            }
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                imagewebp($destination, $filePath, 90);
            }
            break;
        case 'image/gif':
            if (function_exists('imagegif')) {
                imagegif($destination, $filePath);
            }
            break;
    }

    imagedestroy($destination);
    imagedestroy($source);
}

function eliminaDocumentoAtletaDaPercorso(string $path): void
{
    $cleanPath = ltrim(trim($path), '/');
    if ($cleanPath === '') {
        return;
    }

    if (strpos($cleanPath, 'public/atleti/') !== 0) {
        return;
    }

    $absolutePath = __DIR__ . '/../../' . $cleanPath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function eliminaImmagineAtletaDaPercorso(string $path): void
{
    $cleanPath = ltrim(trim($path), '/');
    if ($cleanPath === '') {
        return;
    }

    if (strpos($cleanPath, 'public/atleti/') !== 0) {
        return;
    }

    $absolutePath = __DIR__ . '/../../' . $cleanPath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function normalizzaStringaPerCf(string $value): string
{
    $value = strtoupper(trim($value));
    $map = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
    ];

    $value = strtr($value, $map);

    return preg_replace('/[^A-Z]/', '', $value) ?? '';
}

function calcolaCodiceNomeCognomeCf(string $value, bool $isNome = false): string
{
    $normalized = normalizzaStringaPerCf($value);
    $consonanti = preg_replace('/[AEIOU]/', '', $normalized) ?? '';
    $vocali = preg_replace('/[^AEIOU]/', '', $normalized) ?? '';

    if ($isNome && strlen($consonanti) >= 4) {
        $consonanti = $consonanti[0] . $consonanti[2] . $consonanti[3];
    }

    $out = $consonanti . $vocali . 'XXX';

    return substr($out, 0, 3);
}

function codiceMeseCf(int $month): string
{
    $map = [
        1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'H',
        7 => 'L', 8 => 'M', 9 => 'P', 10 => 'R', 11 => 'S', 12 => 'T',
    ];

    if (!isset($map[$month])) {
        throw new \RuntimeException('Mese data nascita non valido');
    }

    return $map[$month];
}

function codiceComuneCf(string $city, string $province, string $country): string
{
    $countryNormalized = normalizzaStringaPerCf($country);

    if ($countryNormalized !== '' && !in_array($countryNormalized, ['ITALIA', 'IT'], true)) {
        throw new \RuntimeException('Calcolo automatico disponibile solo per nati in Italia');
    }

    $city = trim($city);
    if ($city === '') {
        throw new \RuntimeException('Citta di nascita obbligatoria per calcolare il codice fiscale');
    }

    $province = strtoupper(trim($province));
    $pdo = db_connection();
    $stmt = $pdo->prepare(
        'SELECT codicenazionale
         FROM comuni_italiani
         WHERE comune = :comune
           AND (:provincia = "" OR provincia = :provincia)
         LIMIT 1'
    );
    $stmt->execute([
        'comune' => $city,
        'provincia' => $province,
    ]);

    $code = (string) $stmt->fetchColumn();
    $code = strtoupper(trim($code));

    if ($code === '' || strlen($code) !== 4) {
        throw new \RuntimeException('Comune di nascita non trovato: verifica citta e provincia');
    }

    return $code;
}

function carattereControlloCf(string $partial): string
{
    $oddMap = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
        'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14,
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
    ];
    $evenMap = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9,
        'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
        'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25,
    ];

    $sum = 0;
    $partial = strtoupper($partial);

    for ($i = 0; $i < strlen($partial); $i++) {
        $char = $partial[$i];
        if (!isset($oddMap[$char]) || !isset($evenMap[$char])) {
            throw new \RuntimeException('Dati non validi per calcolo codice fiscale');
        }

        $position = $i + 1;
        $sum += $position % 2 === 1 ? $oddMap[$char] : $evenMap[$char];
    }

    return chr(($sum % 26) + 65);
}

function calcolaCodiceFiscale(array $input): string
{
    $nome = trim((string) ($input['nome'] ?? ''));
    $cognome = trim((string) ($input['cognome'] ?? ''));
    $sesso = strtoupper(trim((string) ($input['sesso'] ?? '')));
    $dataNascita = trim((string) ($input['data_nascita'] ?? ''));
    $cittaNascita = trim((string) ($input['citta_nascita'] ?? ''));
    $provinciaNascita = trim((string) ($input['provincia_nascita'] ?? ''));
    $statoNascita = trim((string) ($input['stato_nascita'] ?? ''));

    if ($nome === '' || $cognome === '' || $sesso === '' || $dataNascita === '') {
        throw new \RuntimeException('Dati anagrafici incompleti per il calcolo del codice fiscale');
    }

    if (!in_array($sesso, ['M', 'F'], true)) {
        throw new \RuntimeException('Il sesso deve essere M o F per calcolare il codice fiscale');
    }

    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dataNascita);
    if (!$date instanceof \DateTimeImmutable || $date->format('Y-m-d') !== $dataNascita) {
        throw new \RuntimeException('Data nascita non valida (formato richiesto YYYY-MM-DD)');
    }

    $year = (int) $date->format('y');
    $month = (int) $date->format('n');
    $day = (int) $date->format('d');
    if ($sesso === 'F') {
        $day += 40;
    }

    $partial = '';
    $partial .= calcolaCodiceNomeCognomeCf($cognome, false);
    $partial .= calcolaCodiceNomeCognomeCf($nome, true);
    $partial .= str_pad((string) $year, 2, '0', STR_PAD_LEFT);
    $partial .= codiceMeseCf($month);
    $partial .= str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    $partial .= codiceComuneCf($cittaNascita, $provinciaNascita, $statoNascita);

    return $partial . carattereControlloCf($partial);
}

/**
 * @return array<int, array{comune:string, provincia:string, nazione:string, cap:string}>
 */
function cercaComuniItaliani(string $query): array
{
    $query = trim($query);
    if ($query === '' || mb_strlen($query) < 2) {
        return [];
    }

    $pdo = db_connection();
    $stmt = $pdo->prepare(
              'SELECT comune, provincia, nazione, cap
         FROM comuni_italiani
         WHERE comune LIKE :search
                GROUP BY comune, provincia, nazione, cap
         ORDER BY comune ASC
         LIMIT 30'
    );
    $stmt->execute([
        'search' => '%' . $query . '%',
    ]);

    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (!is_array($rows)) {
        return [];
    }

    return array_values(array_map(static function (array $row): array {
        return [
            'comune' => trim((string) ($row['comune'] ?? '')),
            'provincia' => strtoupper(trim((string) ($row['provincia'] ?? ''))),
            'nazione' => trim((string) ($row['nazione'] ?? '')),
            'cap' => trim((string) ($row['cap'] ?? '')),
        ];
    }, $rows));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (string) ($_GET['action'] ?? '') === 'search_comuni') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'results' => cercaComuniItaliani((string) ($_GET['q'] ?? '')),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (string) ($_GET['action'] ?? '') === 'calcola_codice_fiscale') {
    try {
        $cf = calcolaCodiceFiscale($_GET);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'codice_fiscale' => $cf,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Throwable $e) {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['form_action'] ?? 'add'));

    if ($action === 'status') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $status = trim((string) ($_POST['status'] ?? ''));
            $atleti->updateAtletaStatus($id, $status);
        }

        redirect_atleti([
            'ok' => 'Stato atleta aggiornato',
        ]);
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $atleti->deleteAtleta($id);
        }

        redirect_atleti([
            'ok' => 'Atleta eliminato con successo',
        ]);
    }

    try {
        if ($action === 'update') {
            $request = new UpdateAtletaRequest($_POST);
            $athleteId = $request->getInt('id');
            $existingAthlete = $atleti->findAtletaById($athleteId);
            if (!is_array($existingAthlete)) {
                throw new \RuntimeException('Atleta non trovato');
            }

            $currentImagePath = trim((string) ($existingAthlete['image_path'] ?? ($_POST['current_image_path'] ?? '')));
            $removeImage = ((string) ($_POST['remove_image'] ?? '0')) === '1';

            $atleti->updateAtleta($athleteId, $request->all());

            $newImagePath = $currentImagePath;
            if ($removeImage) {
                $newImagePath = '';
            }

            $croppedImageData = trim((string) ($_POST['crop_image_base64'] ?? ''));
            if ($croppedImageData !== '') {
                $uploadedPath = gestisciUploadImmagineBase64Atleta($croppedImageData, $athleteId);
                if ($uploadedPath !== '') {
                    $newImagePath = $uploadedPath;
                }
            }

            if ($croppedImageData === '' && isset($_FILES['image']) && is_array($_FILES['image'])) {
                $uploadedPath = gestisciUploadImmagineAtleta($_FILES['image'], $athleteId);
                if ($uploadedPath !== '') {
                    $newImagePath = $uploadedPath;
                }
            }

            if ($newImagePath !== $currentImagePath) {
                $atleti->updateAtletaImage($athleteId, $newImagePath);
                if ($currentImagePath !== '') {
                    eliminaImmagineAtletaDaPercorso($currentImagePath);
                }
            }

            redirect_atleti([
                'ok' => 'Scheda atleta aggiornata',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => trim((string) ($_POST['athlete_tab'] ?? 'anagrafica')),
            ]);
        }

        if ($action === 'add_documento') {
            $request = new AddDocumentoAtletaRequest($_POST);
            $athleteId = $request->getInt('idatleta');
            $payload = $request->all();
            $uploadedDocumentPath = gestisciUploadDocumentoAtleta($_FILES['document_file'] ?? [], $athleteId);
            $manualUrl = trim((string) ($payload['url_documento'] ?? ''));

            if ($uploadedDocumentPath === '' && $manualUrl === '') {
                throw new \RuntimeException('Carica un file documento oppure inserisci un URL');
            }

            $payload['url_documento'] = $uploadedDocumentPath !== '' ? $uploadedDocumentPath : $manualUrl;
            $atleti->addDocumentoAtleta($athleteId, $payload);

            redirect_atleti([
                'ok' => 'Documento aggiunto',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => 'documenti',
            ]);
        }

        if ($action === 'update_documento') {
            $request = new AddDocumentoAtletaRequest($_POST);
            $athleteId = $request->getInt('idatleta');
            $documentId = (int) ($_POST['iddocumento'] ?? 0);
            $payload = $request->all();

            $currentDocument = $atleti->findDocumentoAtletaById($documentId, $athleteId);
            if ($currentDocument === null) {
                throw new \RuntimeException('Documento non trovato');
            }

            $uploadedDocumentPath = gestisciUploadDocumentoAtleta($_FILES['document_file'] ?? [], $athleteId);
            $manualUrl = trim((string) ($payload['url_documento'] ?? ''));

            if ($uploadedDocumentPath !== '') {
                $oldPath = trim((string) ($currentDocument['url_documento'] ?? ''));
                if ($oldPath !== '' && strpos($oldPath, 'public/atleti/') === 0) {
                    eliminaDocumentoAtletaDaPercorso($oldPath);
                }
                $payload['url_documento'] = $uploadedDocumentPath;
            } elseif ($manualUrl === '') {
                $payload['url_documento'] = trim((string) ($currentDocument['url_documento'] ?? ''));
            }

            $atleti->updateDocumentoAtleta($documentId, $athleteId, $payload);

            redirect_atleti([
                'ok' => 'Documento aggiornato',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => 'documenti',
            ]);
        }

        if ($action === 'delete_documento') {
            $athleteId = (int) ($_POST['idatleta'] ?? 0);
            $documentId = (int) ($_POST['iddocumento'] ?? 0);
            $currentDocument = $atleti->findDocumentoAtletaById($documentId, $athleteId);
            if ($currentDocument === null) {
                throw new \RuntimeException('Documento non trovato');
            }

            $atleti->deleteDocumentoAtleta($documentId, $athleteId);

            $oldPath = trim((string) ($currentDocument['url_documento'] ?? ''));
            if ($oldPath !== '' && strpos($oldPath, 'public/atleti/') === 0) {
                eliminaDocumentoAtletaDaPercorso($oldPath);
            }

            redirect_atleti([
                'ok' => 'Documento eliminato',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => 'documenti',
            ]);
        }

        if ($action === 'add_iscrizione') {
            $request = new AddIscrizioneAtletaRequest($_POST);
            $athleteId = $request->getInt('idatleta');
            $atleti->addIscrizioneAtleta($athleteId, $request->all());

            redirect_atleti([
                'ok' => 'Iscrizione aggiunta',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => 'iscrizioni',
            ]);
        }

        if ($action === 'add_pagamento') {
            $request = new AddPagamentoAtletaRequest($_POST);
            $athleteId = $request->getInt('idatleta');
            $atleti->addPagamentoAtleta($athleteId, $request->all());

            redirect_atleti([
                'ok' => 'Pagamento registrato',
                'open_edit' => '1',
                'edit_id' => $athleteId,
                'athlete_tab' => 'pagamenti',
            ]);
        }

        $request = new AddAtletaRequest($_POST);
        $athlete = $atleti->createAtleta($request->all());

        $createdAthleteId = (int) ($athlete['id'] ?? 0);
        $addCroppedImageData = trim((string) ($_POST['crop_image_base64_add'] ?? ''));
        if ($createdAthleteId > 0 && $addCroppedImageData !== '') {
            $newImagePath = gestisciUploadImmagineBase64Atleta($addCroppedImageData, $createdAthleteId);
            if ($newImagePath !== '') {
                $atleti->updateAtletaImage($createdAthleteId, $newImagePath);
            }
        } elseif ($createdAthleteId > 0 && isset($_FILES['image']) && is_array($_FILES['image'])) {
            $newImagePath = gestisciUploadImmagineAtleta($_FILES['image'], $createdAthleteId);
            if ($newImagePath !== '') {
                $atleti->updateAtletaImage($createdAthleteId, $newImagePath);
            }
        }

        redirect_atleti([
            'ok' => 'Atleta creato con successo',
            'open_edit' => '1',
            'edit_id' => (int) ($athlete['id'] ?? 0),
        ]);
    } catch (ValidationException $e) {
        if ($action === 'update') {
            handle_validation_errors(
                $e->errors(),
                'atleti',
                [
                    'open_edit' => '1',
                    'edit_id' => $_POST['id'] ?? 0,
                    'athlete_tab' => $_POST['athlete_tab'] ?? 'anagrafica',
                ]
            );
        }

        if ($action === 'add_documento') {
            handle_validation_errors(
                $e->errors(),
                'atleti',
                [
                    'open_edit' => '1',
                    'edit_id' => $_POST['idatleta'] ?? 0,
                    'athlete_tab' => 'documenti',
                ]
            );
        }

        if ($action === 'add_iscrizione') {
            handle_validation_errors(
                $e->errors(),
                'atleti',
                [
                    'open_edit' => '1',
                    'edit_id' => $_POST['idatleta'] ?? 0,
                    'athlete_tab' => 'iscrizioni',
                ]
            );
        }

        if ($action === 'add_pagamento') {
            handle_validation_errors(
                $e->errors(),
                'atleti',
                [
                    'open_edit' => '1',
                    'edit_id' => $_POST['idatleta'] ?? 0,
                    'athlete_tab' => 'pagamenti',
                ]
            );
        }

        $addErrorContext = [
            'open_add' => '1',
            'athlete_tab' => $_POST['athlete_tab'] ?? 'anagrafica',
        ];

        foreach ($_POST as $key => $value) {
            if (!is_string($key) || !is_scalar($value)) {
                continue;
            }

            if (in_array($key, ['action', 'id', 'page', 'open_add'], true)) {
                continue;
            }

            $addErrorContext['add_' . $key] = (string) $value;
        }

        handle_validation_errors(
            $e->errors(),
            'atleti',
            $addErrorContext
        );
    } catch (\Throwable $e) {
        $targetTab = match ($action) {
            'add_documento' => 'documenti',
            'update_documento' => 'documenti',
            'delete_documento' => 'documenti',
            'add_iscrizione' => 'iscrizioni',
            'add_pagamento' => 'pagamenti',
            default => trim((string) ($_POST['athlete_tab'] ?? 'anagrafica')),
        };

        $redirectParams = [
            'err' => $e->getMessage() !== '' ? $e->getMessage() : 'Operazione non completata',
        ];

        if ($action === 'update' || $action === 'add_documento' || $action === 'update_documento' || $action === 'delete_documento' || $action === 'add_iscrizione' || $action === 'add_pagamento') {
            $redirectParams['open_edit'] = '1';
            $redirectParams['edit_id'] = (string) ((int) ($_POST['id'] ?? $_POST['idatleta'] ?? 0));
            $redirectParams['athlete_tab'] = $targetTab;
        } else {
            $redirectParams['open_add'] = '1';
            $redirectParams['athlete_tab'] = $targetTab;
        }

        redirect_atleti($redirectParams);
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

    $page = $atleti->readAtletiPage($start, $length, $search, $orderColumn, $orderDir);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int) ($page['total'] ?? 0),
        'recordsFiltered' => (int) ($page['filtered'] ?? 0),
        'data' => $page['data'] ?? [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) ($_GET['id'] ?? 0);
    $athlete = $atleti->findAtletaById($id);

    if ($athlete === null) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Atleta non trovato'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($athlete, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($atleti->readAtleti(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
