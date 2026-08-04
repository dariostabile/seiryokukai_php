<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/**
 * Normalizza un'immagine in formato quadrato 512x512 mantenendo il centro.
 */
function normalize_square_image(string $filePath, string $mime): bool
{
    if (!is_file($filePath) || !function_exists('imagecreatetruecolor')) {
        return false;
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
        return false;
    }

    $srcWidth = imagesx($source);
    $srcHeight = imagesy($source);
    if ($srcWidth <= 0 || $srcHeight <= 0) {
        imagedestroy($source);
        return false;
    }

    $cropSize = min($srcWidth, $srcHeight);
    $srcX = (int) floor(($srcWidth - $cropSize) / 2);
    $srcY = (int) floor(($srcHeight - $cropSize) / 2);
    $destinationSize = 512;

    $destination = imagecreatetruecolor($destinationSize, $destinationSize);
    if (!$destination) {
        imagedestroy($source);
        return false;
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

    $saved = false;
    switch ($mime) {
        case 'image/jpeg':
            $saved = function_exists('imagejpeg') ? imagejpeg($destination, $filePath, 90) : false;
            break;
        case 'image/png':
            $saved = function_exists('imagepng') ? imagepng($destination, $filePath, 6) : false;
            break;
        case 'image/webp':
            $saved = function_exists('imagewebp') ? imagewebp($destination, $filePath, 90) : false;
            break;
        case 'image/gif':
            $saved = function_exists('imagegif') ? imagegif($destination, $filePath) : false;
            break;
    }

    imagedestroy($destination);
    imagedestroy($source);

    return $saved;
}

$pdo = db_connection();
$stmt = $pdo->query(
    "SELECT idatleta, COALESCE(immagine_atleta, '') AS immagine_atleta
     FROM atleti
     WHERE cancellato = 0
       AND COALESCE(immagine_atleta, '') <> ''"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$processed = 0;
$missing = 0;
$unsupported = 0;
$errors = 0;

$finfo = new finfo(FILEINFO_MIME_TYPE);
$supported = [
    'image/jpeg' => true,
    'image/png' => true,
    'image/webp' => true,
    'image/gif' => true,
];

foreach ($rows as $row) {
    $id = (int) ($row['idatleta'] ?? 0);
    $relativePath = ltrim(trim((string) ($row['immagine_atleta'] ?? '')), '/');
    if ($relativePath === '' || strpos($relativePath, 'public/atleti/') !== 0) {
        $unsupported++;
        continue;
    }

    $absolutePath = __DIR__ . '/../' . $relativePath;
    if (!is_file($absolutePath)) {
        $missing++;
        continue;
    }

    $mime = (string) $finfo->file($absolutePath);
    if (!isset($supported[$mime])) {
        $unsupported++;
        continue;
    }

    try {
        if (normalize_square_image($absolutePath, $mime)) {
            $processed++;
        } else {
            $errors++;
            fwrite(STDERR, "[WARN] atleta #{$id}: normalizzazione non riuscita per {$relativePath}\n");
        }
    } catch (Throwable $e) {
        $errors++;
        fwrite(STDERR, "[ERR] atleta #{$id}: {$e->getMessage()}\n");
    }
}

echo "Immagini trovate: " . count($rows) . PHP_EOL;
echo "Processate: {$processed}" . PHP_EOL;
echo "File mancanti: {$missing}" . PHP_EOL;
echo "Non supportate/non locali: {$unsupported}" . PHP_EOL;
echo "Errori: {$errors}" . PHP_EOL;
