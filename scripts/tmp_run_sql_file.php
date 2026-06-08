<?php

declare(strict_types=1);

require __DIR__ . '/../config/config.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/tmp_run_sql_file.php <sql-file>" . PHP_EOL);
    exit(1);
}

$inputPath = $argv[1];
$sqlPath = str_starts_with($inputPath, '/') ? $inputPath : __DIR__ . '/../' . ltrim($inputPath, '/');

if (!is_file($sqlPath)) {
    fwrite(STDERR, 'SQL file not found: ' . $sqlPath . PHP_EOL);
    exit(1);
}

$sql = file_get_contents($sqlPath);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, 'SQL file empty or unreadable: ' . $sqlPath . PHP_EOL);
    exit(1);
}

$pdo = db_connection();

try {
    $pdo->exec($sql);
    echo 'OK executed: ' . $sqlPath . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR executing SQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
