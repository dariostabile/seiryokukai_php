<?php

declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$pdo = db_connection();
$queries = [
    'iscrizioni_total' => 'SELECT COUNT(*) AS cnt FROM iscrizioni',
    'iscrizioni_has_corsi_total' => 'SELECT COUNT(*) AS cnt FROM iscrizioni_has_corsi',
    'pagamenti_total' => 'SELECT COUNT(*) AS cnt FROM pagamenti',
    'iscrizioni_missing_courses' => 'SELECT COUNT(*) AS cnt FROM iscrizioni i LEFT JOIN iscrizioni_has_corsi ihc ON ihc.idiscrizione = i.idiscrizione WHERE ihc.idiscrizione IS NULL',
];

foreach ($queries as $label => $sql) {
    try {
        $stmt = $pdo->query($sql);
        $count = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
        echo $label . '=' . $count . PHP_EOL;
    } catch (Throwable $e) {
        echo $label . '=ERROR(' . $e->getMessage() . ')' . PHP_EOL;
    }
}
