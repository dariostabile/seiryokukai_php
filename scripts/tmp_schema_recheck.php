<?php

declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$pdo = db_connection();
$tables = ['iscrizioni', 'iscrizioni_has_corsi', 'pagamenti'];

foreach ($tables as $table) {
    echo '[' . $table . ']' . PHP_EOL;

    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);
    if (!$stmt->fetchColumn()) {
        echo "(missing)" . PHP_EOL . PHP_EOL;
        continue;
    }

    $columns = $pdo->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo $column['Field'] . ':' . $column['Type'] . PHP_EOL;
    }

    echo PHP_EOL;
}
