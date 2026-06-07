<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/lib/data.php';

use App\Services\AtletiService;

function out(string $message): void
{
    echo $message . PHP_EOL;
}

try {
    $pdo = db_connection();
    $service = new AtletiService();

    out('== Smoke test: data_iscrizione_corso ==');

    $columnCheck = $pdo->query("SHOW COLUMNS FROM `iscrizioni_has_corsi` LIKE 'data_iscrizione_corso'")->fetch();
    if (!$columnCheck) {
        throw new RuntimeException('Colonna data_iscrizione_corso assente in iscrizioni_has_corsi');
    }
    out('OK: colonna presente');

    $pairStmt = $pdo->query(
        'SELECT a.idatleta, c.idcorso
         FROM atleti a
         INNER JOIN corsi c ON 1 = 1
         LEFT JOIN iscrizioni i ON i.idatleta = a.idatleta
         LEFT JOIN iscrizioni_has_corsi ihc ON ihc.idiscrizione = i.idiscrizione AND ihc.idcorso = c.idcorso
         WHERE a.cancellato = 0
           AND ihc.idcorso IS NULL
         ORDER BY a.idatleta ASC, c.idcorso ASC
         LIMIT 1'
    );
    $pair = $pairStmt ? $pairStmt->fetch(PDO::FETCH_ASSOC) : false;

    if (!is_array($pair)) {
        out('SKIP: nessuna coppia atleta/corso libera trovata per test add/cleanup');
        exit(0);
    }

    $idAtleta = (int) ($pair['idatleta'] ?? 0);
    $idCorso = (int) ($pair['idcorso'] ?? 0);
    if ($idAtleta <= 0 || $idCorso <= 0) {
        throw new RuntimeException('Coppia atleta/corso non valida per smoke test');
    }

    $testDate = (new DateTimeImmutable('today'))->format('Y-m-d');

    $payload = [
        'course_ids' => [$idCorso],
        'data_inizio_iscrizione' => $testDate,
        'data_fine_iscrizione' => null,
        'totale_iscrizione' => 0,
        'stato_iscrizione' => 'A',
        'note_iscrizione' => 'smoke-test-data-corso',
        'data_iscrizione_corso' => $testDate,
    ];

    $service->addIscrizioneAtleta($idAtleta, $payload);

    $findStmt = $pdo->prepare(
        'SELECT ihc.idiscrizione, ihc.data_iscrizione_corso
         FROM iscrizioni_has_corsi ihc
         INNER JOIN iscrizioni i ON i.idiscrizione = ihc.idiscrizione
         WHERE i.idatleta = :idatleta
           AND ihc.idcorso = :idcorso
         ORDER BY ihc.idiscrizione DESC
         LIMIT 1'
    );
    $findStmt->execute([
        'idatleta' => $idAtleta,
        'idcorso' => $idCorso,
    ]);
    $created = $findStmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($created)) {
        throw new RuntimeException('Impossibile rilevare iscrizione appena creata');
    }

    $idIscrizione = (int) ($created['idiscrizione'] ?? 0);
    $savedDate = (string) ($created['data_iscrizione_corso'] ?? '');

    if ($savedDate !== $testDate) {
        throw new RuntimeException('Valore data_iscrizione_corso non coerente dopo add: atteso ' . $testDate . ', trovato ' . $savedDate);
    }
    out('OK: addIscrizioneAtleta salva data_iscrizione_corso');

    $newDate = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
    $payload['data_iscrizione_corso'] = $newDate;
    $payload['note_iscrizione'] = 'smoke-test-data-corso-update';

    $updated = $service->updateIscrizioneAtleta($idAtleta, $idIscrizione, $payload);
    if (!$updated) {
        throw new RuntimeException('updateIscrizioneAtleta ha restituito false');
    }

    $findStmt->execute([
        'idatleta' => $idAtleta,
        'idcorso' => $idCorso,
    ]);
    $afterUpdate = $findStmt->fetch(PDO::FETCH_ASSOC);
    $updatedDate = is_array($afterUpdate) ? (string) ($afterUpdate['data_iscrizione_corso'] ?? '') : '';
    if ($updatedDate !== $newDate) {
        throw new RuntimeException('Valore data_iscrizione_corso non coerente dopo update: atteso ' . $newDate . ', trovato ' . $updatedDate);
    }
    out('OK: updateIscrizioneAtleta aggiorna data_iscrizione_corso');

    if ($idIscrizione > 0) {
        $cleanupPagamenti = $pdo->prepare('DELETE FROM pagamenti WHERE idiscrizione = :idiscrizione');
        $cleanupPagamenti->execute(['idiscrizione' => $idIscrizione]);

        $cleanupLink = $pdo->prepare('DELETE FROM iscrizioni_has_corsi WHERE idiscrizione = :idiscrizione');
        $cleanupLink->execute(['idiscrizione' => $idIscrizione]);

        $cleanupIscr = $pdo->prepare('DELETE FROM iscrizioni WHERE idiscrizione = :idiscrizione');
        $cleanupIscr->execute(['idiscrizione' => $idIscrizione]);
    }

    out('OK: cleanup completato');
    out('Smoke test completato con successo');
    exit(0);
} catch (Throwable $e) {
    out('ERRORE: ' . $e->getMessage());
    exit(1);
}
