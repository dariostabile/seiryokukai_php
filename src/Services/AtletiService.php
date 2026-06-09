<?php

declare(strict_types=1);

namespace App\Services;

final class AtletiService extends BaseService
{
    private const DB_SCHEMA_REFERENCE = 'database/seiryokukai_20260604_mod.sql + database/alter_add_iscrizioni_table.sql + database/alter_iscrizioni_has_corsi_add_data_iscrizione_corso.sql';

    public function readAtleti(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idatleta AS id,
                TRIM(CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, ''))) AS name,
                CASE WHEN attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(email_1, '') AS email,
                COALESCE(telefono_1, '') AS phone,
                COALESCE(immagine_atleta, '') AS image_path
             FROM atleti
             WHERE cancellato = 0
             ORDER BY idatleta DESC"
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (is_array($rows)) {
            foreach ($rows as &$row) {
                $row['image_url'] = $this->toPublicUrl((string) ($row['image_path'] ?? ''));
            }
            unset($row);
        }

        return is_array($rows) ? $rows : [];
    }

    public function readAtletiPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        $pdo = db_connection();

        $start = max(0, $start);
        $length = $length > 0 ? $length : 10;

        $allowedOrder = [
            'id' => 'idatleta',
            'name' => "TRIM(CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, '')))",
            'email' => 'email_1',
            'phone' => 'telefono_1',
            'status' => 'attivo',
        ];

        $orderSql = $allowedOrder[$orderColumn] ?? 'idatleta';
        $orderDirSql = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $total = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0')->fetchColumn();

        $whereSql = 'WHERE cancellato = 0';
        $params = [];

        if ($search !== '') {
            $whereSql .= ' AND (
                nome LIKE :search
                OR cognome LIKE :search
                OR email_1 LIKE :search
                OR telefono_1 LIKE :search
            )';
            $params['search'] = '%' . $search . '%';
        }

        $countSql = "SELECT COUNT(*) FROM atleti $whereSql";
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $filtered = (int) $countStmt->fetchColumn();

        $sql =
            "SELECT
                idatleta AS id,
                TRIM(CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, ''))) AS name,
                CASE WHEN attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(email_1, '') AS email,
                COALESCE(telefono_1, '') AS phone,
                COALESCE(immagine_atleta, '') AS image_path
             FROM atleti
             $whereSql
             ORDER BY $orderSql $orderDirSql
             LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $length, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $start, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (is_array($rows)) {
            foreach ($rows as &$row) {
                $row['image_url'] = $this->toPublicUrl((string) ($row['image_path'] ?? ''));
            }
            unset($row);
        }

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
    }

    public function addAtleta(string $name, string $plan = ''): array
    {
        return $this->createAtletaFromLegacyName($name);
    }

    public function findAtletaById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                idatleta AS id,
                titolo,
                nome,
                cognome,
                codice_fiscale,
                data_nascita,
                citta_nascita,
                provincia_nascita,
                stato_nascita,
                indirizzo_residenza,
                citta_residenza,
                provincia_residenza,
                cap_residenza,
                stato_residenza,
                sesso,
                telefono_1,
                telefono_2,
                email_1,
                email_2,
                pec,
                piva,
                codice_univoco_fatturazione,
                data_scadenza_account,
                immagine_atleta,
                note_atleta,
                altezza,
                peso,
                misura,
                misura_maglia,
                misura_pantaloni,
                TRIM(CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, ''))) AS name,
                CASE WHEN attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(email_1, '') AS email,
                COALESCE(telefono_1, '') AS phone
             FROM atleti
             WHERE idatleta = :id
                 AND cancellato = 0
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return array_merge(
            $this->mapAtletaRow($row),
            [
                'documenti' => $this->readDocumentiAtleta($id),
                'iscrizioni' => $this->readIscrizioniAtleta($id),
                'pagamenti' => $this->readPagamentiAtleta($id),
            ]
        );
    }

    public function createAtleta(array $payload): array
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO atleti (
                titolo,
                nome,
                cognome,
                codice_fiscale,
                data_nascita,
                citta_nascita,
                provincia_nascita,
                stato_nascita,
                indirizzo_residenza,
                citta_residenza,
                provincia_residenza,
                cap_residenza,
                stato_residenza,
                sesso,
                telefono_1,
                telefono_2,
                email_1,
                email_2,
                pec,
                piva,
                codice_univoco_fatturazione,
                attivo,
                cancellato,
                data_creazione_account,
                data_scadenza_account,
                note_atleta,
                altezza,
                peso,
                misura,
                misura_maglia,
                misura_pantaloni
            ) VALUES (
                :titolo,
                :nome,
                :cognome,
                :codice_fiscale,
                :data_nascita,
                :citta_nascita,
                :provincia_nascita,
                :stato_nascita,
                :indirizzo_residenza,
                :citta_residenza,
                :provincia_residenza,
                :cap_residenza,
                :stato_residenza,
                :sesso,
                :telefono_1,
                :telefono_2,
                :email_1,
                :email_2,
                :pec,
                :piva,
                :codice_univoco_fatturazione,
                :attivo,
                0,
                NOW(),
                :data_scadenza_account,
                :note_atleta,
                :altezza,
                :peso,
                :misura,
                :misura_maglia,
                :misura_pantaloni
            )'
        );

        $stmt->execute($this->buildAtletaParams($payload));

        $id = (int) $pdo->lastInsertId();
        $created = $this->findAtletaById($id);

        if ($created === null) {
            throw new \RuntimeException('Impossibile rileggere l\'atleta appena creato');
        }

        return $created;
    }

    public function updateAtleta(int $id, array $payload): bool
    {
        if ($id <= 0) {
            return false;
        }

        $params = $this->buildAtletaParams($payload);
        $params['id'] = $id;

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'UPDATE atleti SET
                titolo = :titolo,
                nome = :nome,
                cognome = :cognome,
                codice_fiscale = :codice_fiscale,
                data_nascita = :data_nascita,
                citta_nascita = :citta_nascita,
                provincia_nascita = :provincia_nascita,
                stato_nascita = :stato_nascita,
                indirizzo_residenza = :indirizzo_residenza,
                citta_residenza = :citta_residenza,
                provincia_residenza = :provincia_residenza,
                cap_residenza = :cap_residenza,
                stato_residenza = :stato_residenza,
                sesso = :sesso,
                telefono_1 = :telefono_1,
                telefono_2 = :telefono_2,
                email_1 = :email_1,
                email_2 = :email_2,
                pec = :pec,
                piva = :piva,
                codice_univoco_fatturazione = :codice_univoco_fatturazione,
                attivo = :attivo,
                data_scadenza_account = :data_scadenza_account,
                note_atleta = :note_atleta,
                altezza = :altezza,
                peso = :peso,
                misura = :misura,
                misura_maglia = :misura_maglia,
                misura_pantaloni = :misura_pantaloni
             WHERE idatleta = :id
               AND cancellato = 0'
        );

        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function addDocumentoAtleta(int $idAtleta, array $payload): int
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO documenti (
                idatleta,
                idtipo_documento,
                descrizione_documento,
                data_documento,
                data_scadenza,
                url_documento
            ) VALUES (
                :idatleta,
                :idtipo_documento,
                :descrizione_documento,
                :data_documento,
                :data_scadenza,
                :url_documento
            )'
        );
        $stmt->execute([
            'idatleta' => $idAtleta,
            'idtipo_documento' => (int) ($payload['idtipo_documento'] ?? 0),
            'descrizione_documento' => $this->normalizeNullableString($payload['descrizione_documento'] ?? null),
            'data_documento' => $this->normalizeNullableDate($payload['data_documento'] ?? null),
            'data_scadenza' => $this->normalizeNullableDate($payload['data_scadenza'] ?? null),
            'url_documento' => $this->normalizeNullableString($payload['url_documento'] ?? null),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function findDocumentoAtletaById(int $idDocumento, int $idAtleta): ?array
    {
        if ($idDocumento <= 0 || $idAtleta <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                iddocumento AS id,
                idatleta,
                idtipo_documento,
                COALESCE(descrizione_documento, \'\') AS descrizione_documento,
                data_documento,
                data_scadenza,
                COALESCE(url_documento, \'\') AS url_documento
             FROM documenti
             WHERE iddocumento = :iddocumento
               AND idatleta = :idatleta
             LIMIT 1'
        );
        $stmt->execute([
            'iddocumento' => $idDocumento,
            'idatleta' => $idAtleta,
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function updateDocumentoAtleta(int $idDocumento, int $idAtleta, array $payload): bool
    {
        if ($idDocumento <= 0 || $idAtleta <= 0) {
            return false;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'UPDATE documenti SET
                idtipo_documento = :idtipo_documento,
                descrizione_documento = :descrizione_documento,
                data_documento = :data_documento,
                data_scadenza = :data_scadenza,
                url_documento = :url_documento
             WHERE iddocumento = :iddocumento
               AND idatleta = :idatleta'
        );
        $stmt->execute([
            'idtipo_documento' => (int) ($payload['idtipo_documento'] ?? 0),
            'descrizione_documento' => $this->normalizeNullableString($payload['descrizione_documento'] ?? null),
            'data_documento' => $this->normalizeNullableDate($payload['data_documento'] ?? null),
            'data_scadenza' => $this->normalizeNullableDate($payload['data_scadenza'] ?? null),
            'url_documento' => $this->normalizeNullableString($payload['url_documento'] ?? null),
            'iddocumento' => $idDocumento,
            'idatleta' => $idAtleta,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteDocumentoAtleta(int $idDocumento, int $idAtleta): bool
    {
        if ($idDocumento <= 0 || $idAtleta <= 0) {
            return false;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'DELETE FROM documenti
             WHERE iddocumento = :iddocumento
               AND idatleta = :idatleta'
        );
        $stmt->execute([
            'iddocumento' => $idDocumento,
            'idatleta' => $idAtleta,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function addIscrizioneAtleta(int $idAtleta, array $payload): int
    {
        $this->assertLatestDatabaseSchema();
        $iscrizioniColumns = $this->resolveIscrizioniColumnNames();

        $courseIds = array_values(array_filter(array_map('intval', (array) ($payload['course_ids'] ?? [])), static fn (int $value): bool => $value > 0));
        if ($courseIds === []) {
            throw new \InvalidArgumentException('Seleziona almeno un corso per l\'iscrizione');
        }

        $dataIscrizioneCorso = $this->normalizeNullableDate($payload['data_inizio_iscrizione'] ?? null);

        $pdo = db_connection();

        $duplicateStmt = $pdo->prepare(
            'SELECT 1
             FROM iscrizioni_has_corsi ihc
             INNER JOIN iscrizioni i ON i.idiscrizione = ihc.idiscrizione
             WHERE i.idatleta = :idatleta
               AND ihc.idcorso = :idcorso
             LIMIT 1'
        );
        foreach ($courseIds as $courseId) {
            $duplicateStmt->execute([
                'idatleta' => $idAtleta,
                'idcorso' => $courseId,
            ]);
            if ($duplicateStmt->fetchColumn() !== false) {
                throw new \RuntimeException('Esiste gia una iscrizione per questo corso.');
            }
        }

        $insertIscrizioneStmt = $pdo->prepare(
            'INSERT INTO iscrizioni (
                idatleta,
                ' . $iscrizioniColumns['start'] . ',
                ' . $iscrizioniColumns['end'] . ',
                abbonamento,
                ' . $iscrizioniColumns['total'] . ',
                stato_iscrizione,
                note_iscrizione
            ) VALUES (
                :idatleta,
                :start_date,
                :end_date,
                :abbonamento,
                :total,
                :stato_iscrizione,
                :note_iscrizione
            )'
        );
        $insertCorsoStmt = $pdo->prepare(
            'INSERT INTO iscrizioni_has_corsi (idiscrizione, idcorso, data_iscrizione_corso, note)
             VALUES (:idiscrizione, :idcorso, :data_iscrizione_corso, :note)
             ON DUPLICATE KEY UPDATE
                data_iscrizione_corso = VALUES(data_iscrizione_corso),
                note = VALUES(note)'
        );

        $pdo->beginTransaction();
        try {
            $insertIscrizioneStmt->execute([
                'idatleta' => $idAtleta,
                'start_date' => $this->normalizeNullableDate($payload['data_inizio_iscrizione'] ?? null),
                'end_date' => $this->normalizeNullableDate($payload['data_fine_iscrizione'] ?? null),
                'abbonamento' => $this->normalizeNullableInt($payload['abbonamento'] ?? 1),
                'total' => $this->normalizeNullableFloat($payload['totale_abbonamento'] ?? ($payload['totale_iscrizione'] ?? null)),
                'stato_iscrizione' => $this->normalizeNullableString($payload['stato_iscrizione'] ?? null),
                'note_iscrizione' => $this->normalizeNullableString($payload['note_iscrizione'] ?? null),
            ]);
            $idIscrizione = (int) $pdo->lastInsertId();

            foreach ($courseIds as $courseId) {
                $insertCorsoStmt->execute([
                    'idiscrizione' => $idIscrizione,
                    'idcorso' => $courseId,
                    'data_iscrizione_corso' => $dataIscrizioneCorso,
                    'note' => $this->normalizeNullableString($payload['note_iscrizione'] ?? null),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }

        return count($courseIds);
    }

    public function updateIscrizioneAtleta(int $idAtleta, int $idIscrizione, array $payload): bool
    {
        $this->assertLatestDatabaseSchema();
        $iscrizioniColumns = $this->resolveIscrizioniColumnNames();

        $courseIds = array_values(array_unique(array_filter(array_map('intval', (array) ($payload['course_ids'] ?? [])), static fn (int $value): bool => $value > 0)));
        if ($idAtleta <= 0 || $idIscrizione <= 0 || $courseIds === []) {
            return false;
        }

        $dataIscrizioneCorso = $this->normalizeNullableDate($payload['data_iscrizione_corso'] ?? ($payload['data_inizio_iscrizione'] ?? null));

        $pdo = db_connection();

        $ownershipStmt = $pdo->prepare(
            'SELECT 1
             FROM iscrizioni
             WHERE idiscrizione = :idiscrizione
               AND idatleta = :idatleta
             LIMIT 1'
        );
        $ownershipStmt->execute([
            'idiscrizione' => $idIscrizione,
            'idatleta' => $idAtleta,
        ]);
        if ($ownershipStmt->fetchColumn() === false) {
            return false;
        }

        $existingCoursesStmt = $pdo->prepare('SELECT idcorso FROM iscrizioni_has_corsi WHERE idiscrizione = :idiscrizione');
        $existingCoursesStmt->execute(['idiscrizione' => $idIscrizione]);
        $existingCourses = array_map('intval', $existingCoursesStmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);

        $pagamentiAssociati = $this->countPagamentiByIscrizione($idIscrizione);
        $normalizedExistingCourses = $existingCourses;
        sort($normalizedExistingCourses);
        $normalizedCourseIds = $courseIds;
        sort($normalizedCourseIds);
        if ($pagamentiAssociati > 0 && $normalizedExistingCourses !== $normalizedCourseIds) {
            throw new \RuntimeException('Impossibile cambiare corsi all\'iscrizione: sono presenti pagamenti associati.');
        }

        $duplicateStmt = $pdo->prepare(
            'SELECT 1
             FROM iscrizioni_has_corsi ihc
             INNER JOIN iscrizioni i ON i.idiscrizione = ihc.idiscrizione
             WHERE i.idatleta = :idatleta
               AND ihc.idcorso = :idcorso
               AND ihc.idiscrizione <> :idiscrizione
             LIMIT 1'
        );
        foreach ($courseIds as $courseId) {
            $duplicateStmt->execute([
                'idatleta' => $idAtleta,
                'idcorso' => $courseId,
                'idiscrizione' => $idIscrizione,
            ]);
            if ($duplicateStmt->fetchColumn() !== false) {
                throw new \RuntimeException('Esiste gia una iscrizione per questo corso.');
            }
        }

        $pdo->beginTransaction();
        try {
            $updateIscrizioneStmt = $pdo->prepare(
                'UPDATE iscrizioni SET
                    ' . $iscrizioniColumns['start'] . ' = :start_date,
                    ' . $iscrizioniColumns['end'] . ' = :end_date,
                    abbonamento = :abbonamento,
                    ' . $iscrizioniColumns['total'] . ' = :total,
                    stato_iscrizione = :stato_iscrizione,
                    note_iscrizione = :note_iscrizione
                 WHERE idiscrizione = :idiscrizione'
            );
            $updateIscrizioneStmt->execute([
                'start_date' => $this->normalizeNullableDate($payload['data_inizio_iscrizione'] ?? null),
                'end_date' => $this->normalizeNullableDate($payload['data_fine_iscrizione'] ?? null),
                'abbonamento' => $this->normalizeNullableInt($payload['abbonamento'] ?? 1),
                'total' => $this->normalizeNullableFloat($payload['totale_abbonamento'] ?? ($payload['totale_iscrizione'] ?? null)),
                'stato_iscrizione' => $this->normalizeNullableString($payload['stato_iscrizione'] ?? null),
                'note_iscrizione' => $this->normalizeNullableString($payload['note_iscrizione'] ?? null),
                'idiscrizione' => $idIscrizione,
            ]);

            $toDelete = array_diff($existingCourses, $courseIds);
            if ($toDelete !== []) {
                $deleteStmt = $pdo->prepare('DELETE FROM iscrizioni_has_corsi WHERE idiscrizione = :idiscrizione AND idcorso = :idcorso');
                foreach ($toDelete as $courseId) {
                    $deleteStmt->execute([
                        'idiscrizione' => $idIscrizione,
                        'idcorso' => (int) $courseId,
                    ]);
                }
            }

            $upsertStmt = $pdo->prepare(
                'INSERT INTO iscrizioni_has_corsi (idiscrizione, idcorso, data_iscrizione_corso, note)
                 VALUES (:idiscrizione, :idcorso, :data_iscrizione_corso, :note)
                 ON DUPLICATE KEY UPDATE
                    data_iscrizione_corso = VALUES(data_iscrizione_corso),
                    note = VALUES(note)'
            );

            foreach ($courseIds as $courseId) {
                $upsertStmt->execute([
                    'idiscrizione' => $idIscrizione,
                    'idcorso' => $courseId,
                    'data_iscrizione_corso' => $dataIscrizioneCorso,
                    'note' => $this->normalizeNullableString($payload['note_iscrizione'] ?? null),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }

        return true;
    }

    public function deleteIscrizioneAtleta(int $idAtleta, int $idIscrizione): bool
    {
        $this->assertLatestDatabaseSchema();

        if ($idAtleta <= 0 || $idIscrizione <= 0) {
            return false;
        }

        $pdo = db_connection();
        $ownershipStmt = $pdo->prepare(
            'SELECT 1
             FROM iscrizioni
             WHERE idiscrizione = :idiscrizione
               AND idatleta = :idatleta
             LIMIT 1'
        );
        $ownershipStmt->execute([
            'idiscrizione' => $idIscrizione,
            'idatleta' => $idAtleta,
        ]);
        if ($ownershipStmt->fetchColumn() === false) {
            return false;
        }

        $pagamentiAssociati = $this->countPagamentiByIscrizione($idIscrizione);
        if ($pagamentiAssociati > 0) {
            throw new \RuntimeException('Impossibile eliminare l\'iscrizione: sono presenti pagamenti associati. Elimina prima i pagamenti collegati.');
        }

        $pdo->beginTransaction();
        try {
            $deleteLinksStmt = $pdo->prepare('DELETE FROM iscrizioni_has_corsi WHERE idiscrizione = :idiscrizione');
            $deleteLinksStmt->execute(['idiscrizione' => $idIscrizione]);

            $deleteIscrizioneStmt = $pdo->prepare('DELETE FROM iscrizioni WHERE idiscrizione = :idiscrizione AND idatleta = :idatleta');
            $deleteIscrizioneStmt->execute([
                'idiscrizione' => $idIscrizione,
                'idatleta' => $idAtleta,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }

        return true;
    }

    public function addPagamentoAtleta(int $idAtleta, array $payload): int
    {
        $this->assertLatestDatabaseSchema();

        $pdo = db_connection();

        $idCorso = (int) ($payload['idcorso'] ?? 0);
        $idIscrizione = $this->findIscrizioneIdByAtletaAndCorso($idAtleta, $idCorso);
        if ($idIscrizione === null) {
            throw new \RuntimeException('Il corso selezionato non risulta tra le iscrizioni dell\'atleta');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO pagamenti (
                idiscrizione,
                data_pagamento,
                data_scadenza,
                quota,
                note_pagamento
            ) VALUES (
                :idiscrizione,
                :data_pagamento,
                :data_scadenza,
                :quota,
                :note_pagamento
            )'
        );
        $stmt->execute([
            'idiscrizione' => $idIscrizione,
            'data_pagamento' => $this->normalizeNullableDate($payload['data_pagamento'] ?? null),
            'data_scadenza' => $this->normalizeNullableDate($payload['data_scadenza'] ?? null),
            'quota' => $this->normalizeNullableFloat($payload['quota_pagamento'] ?? null),
            'note_pagamento' => $this->normalizeNullableString($payload['note_pagamento'] ?? null),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function findPagamentoAtletaById(int $idPagamento, int $idAtleta): ?array
    {
        $this->assertLatestDatabaseSchema();

        if ($idPagamento <= 0 || $idAtleta <= 0) {
            return null;
        }

        $pdo = db_connection();

        $stmt = $pdo->prepare(
            'SELECT
                idpagamento AS id,
                idiscrizione,
                idcorso,
                data_pagamento,
                data_scadenza,
                quota AS quota_pagamento,
                note_pagamento
             FROM (
                SELECT
                    p.idpagamento,
                    p.idiscrizione,
                    (
                        SELECT MIN(ihc.idcorso)
                        FROM iscrizioni_has_corsi ihc
                        WHERE ihc.idiscrizione = p.idiscrizione
                    ) AS idcorso,
                    p.data_pagamento,
                    p.data_scadenza,
                    p.quota,
                    p.note_pagamento,
                    i.idatleta
                FROM pagamenti p
                INNER JOIN iscrizioni i ON i.idiscrizione = p.idiscrizione
             ) p
             WHERE p.idpagamento = :idpagamento
               AND p.idatleta = :idatleta
             LIMIT 1'
        );
        $stmt->execute([
            'idpagamento' => $idPagamento,
            'idatleta' => $idAtleta,
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function updatePagamentoAtleta(int $idPagamento, int $idAtleta, array $payload): bool
    {
        $this->assertLatestDatabaseSchema();

        $current = $this->findPagamentoAtletaById($idPagamento, $idAtleta);
        if ($current === null) {
            return false;
        }

        $courseIdAttuale = (int) ($current['idcorso'] ?? 0);
        if (!$this->isPagamentoUltimoPerCorso($idAtleta, $courseIdAttuale, $idPagamento)) {
            throw new \RuntimeException('Puoi modificare solo l\'ultimo pagamento del corso.');
        }

        $courseIdNuovo = (int) ($payload['idcorso'] ?? 0);
        if ($courseIdNuovo <= 0) {
            return false;
        }

        $pdo = db_connection();

        $idIscrizioneNuova = $this->findIscrizioneIdByAtletaAndCorso($idAtleta, $courseIdNuovo);
        if ($idIscrizioneNuova === null) {
            throw new \RuntimeException('Il corso selezionato non risulta tra le iscrizioni dell\'atleta');
        }

        $stmt = $pdo->prepare(
            'UPDATE pagamenti SET
                idiscrizione = :idiscrizione,
                data_pagamento = :data_pagamento,
                data_scadenza = :data_scadenza,
                quota = :quota,
                note_pagamento = :note_pagamento
             WHERE idpagamento = :idpagamento'
        );
        $stmt->execute([
            'idiscrizione' => $idIscrizioneNuova,
            'data_pagamento' => $this->normalizeNullableDate($payload['data_pagamento'] ?? null),
            'data_scadenza' => $this->normalizeNullableDate($payload['data_scadenza'] ?? null),
            'quota' => $this->normalizeNullableFloat($payload['quota_pagamento'] ?? null),
            'note_pagamento' => $this->normalizeNullableString($payload['note_pagamento'] ?? null),
            'idpagamento' => $idPagamento,
        ]);

        return true;
    }

    public function deletePagamentoAtleta(int $idPagamento, int $idAtleta): bool
    {
        $this->assertLatestDatabaseSchema();

        $current = $this->findPagamentoAtletaById($idPagamento, $idAtleta);
        if ($current === null) {
            return false;
        }

        $courseId = (int) ($current['idcorso'] ?? 0);
        if (!$this->isPagamentoUltimoPerCorso($idAtleta, $courseId, $idPagamento)) {
            throw new \RuntimeException('Puoi eliminare solo l\'ultimo pagamento del corso.');
        }

        $pdo = db_connection();

        $stmt = $pdo->prepare(
            'DELETE p
             FROM pagamenti p
             INNER JOIN iscrizioni i ON i.idiscrizione = p.idiscrizione
             WHERE p.idpagamento = :idpagamento
               AND i.idatleta = :idatleta'
        );
        $stmt->execute([
            'idpagamento' => $idPagamento,
            'idatleta' => $idAtleta,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function isPagamentoUltimoPerCorso(int $idAtleta, int $idCorso, int $idPagamento): bool
    {
        $this->assertLatestDatabaseSchema();

        if ($idAtleta <= 0 || $idCorso <= 0 || $idPagamento <= 0) {
            return false;
        }

        $pdo = db_connection();

        $idIscrizione = $this->findIscrizioneIdByAtletaAndCorso($idAtleta, $idCorso);
        if ($idIscrizione === null) {
            return false;
        }

        $stmt = $pdo->prepare(
            'SELECT idpagamento
             FROM pagamenti
             WHERE idiscrizione = :idiscrizione
             ORDER BY data_pagamento DESC, idpagamento DESC
             LIMIT 1'
        );
        $stmt->execute([
            'idiscrizione' => $idIscrizione,
        ]);

        $lastId = (int) $stmt->fetchColumn();

        return $lastId === $idPagamento;
    }

    public function updateAtletaStatus(int $id, string $status): bool
    {
        $allowedStatuses = ['Attivo', 'Sospeso'];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $activeValue = $status === 'Attivo' ? 1 : 0;

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE atleti SET attivo = :attivo WHERE idatleta = :id AND cancellato = 0');
        $stmt->execute([
            'attivo' => $activeValue,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateAtletaImage(int $id, ?string $imagePath): bool
    {
        if ($id <= 0) {
            return false;
        }

        $cleanPath = $imagePath !== null ? trim($imagePath) : null;
        if ($cleanPath === '') {
            $cleanPath = null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE atleti SET immagine_atleta = :immagine_atleta WHERE idatleta = :idatleta AND cancellato = 0');
        $stmt->execute([
            'immagine_atleta' => $cleanPath,
            'idatleta' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteAtleta(int $id): bool
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE atleti SET cancellato = 1, attivo = 0 WHERE idatleta = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function createAtletaFromLegacyName(string $name): array
    {
        $name = trim($name);
        $parts = preg_split('/\s+/', $name) ?: [];
        $nome = (string) array_shift($parts);
        $cognome = trim(implode(' ', $parts));

        if ($nome === '') {
            throw new \InvalidArgumentException('Il nome atleta non puo essere vuoto');
        }

        return $this->createAtleta([
            'nome' => $nome,
            'cognome' => $cognome,
            'status' => 'Attivo',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function buildAtletaParams(array $payload): array
    {
        return [
            'titolo' => $this->normalizeNullableString($payload['titolo'] ?? null),
            'nome' => trim((string) ($payload['nome'] ?? '')),
            'cognome' => trim((string) ($payload['cognome'] ?? '')),
            'codice_fiscale' => $this->normalizeNullableString($payload['codice_fiscale'] ?? null),
            'data_nascita' => $this->normalizeNullableDate($payload['data_nascita'] ?? null),
            'citta_nascita' => $this->normalizeNullableString($payload['citta_nascita'] ?? null),
            'provincia_nascita' => $this->normalizeNullableString($payload['provincia_nascita'] ?? null),
            'stato_nascita' => $this->normalizeNullableString($payload['stato_nascita'] ?? null),
            'indirizzo_residenza' => $this->normalizeNullableString($payload['indirizzo_residenza'] ?? null),
            'citta_residenza' => $this->normalizeNullableString($payload['citta_residenza'] ?? null),
            'provincia_residenza' => $this->normalizeNullableString($payload['provincia_residenza'] ?? null),
            'cap_residenza' => $this->normalizeNullableString($payload['cap_residenza'] ?? null),
            'stato_residenza' => $this->normalizeNullableString($payload['stato_residenza'] ?? null),
            'sesso' => $this->normalizeNullableString($payload['sesso'] ?? null),
            'telefono_1' => $this->normalizeNullableString($payload['telefono_1'] ?? null),
            'telefono_2' => $this->normalizeNullableString($payload['telefono_2'] ?? null),
            'email_1' => $this->normalizeNullableString($payload['email_1'] ?? null),
            'email_2' => $this->normalizeNullableString($payload['email_2'] ?? null),
            'pec' => $this->normalizeNullableString($payload['pec'] ?? null),
            'piva' => $this->normalizeNullableString($payload['piva'] ?? null),
            'codice_univoco_fatturazione' => $this->normalizeNullableString($payload['codice_univoco_fatturazione'] ?? null),
            'attivo' => $this->statusToBool((string) ($payload['status'] ?? 'Attivo')),
            'data_scadenza_account' => $this->normalizeNullableDate($payload['data_scadenza_account'] ?? null),
            'note_atleta' => $this->normalizeNullableString($payload['note_atleta'] ?? null),
            'altezza' => $this->normalizeNullableInt($payload['altezza'] ?? null),
            'peso' => $this->normalizeNullableFloat($payload['peso'] ?? null),
            'misura' => $this->normalizeNullableString($payload['misura'] ?? null),
            'misura_maglia' => $this->normalizeNullableString($payload['misura_maglia'] ?? null),
            'misura_pantaloni' => $this->normalizeNullableString($payload['misura_pantaloni'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapAtletaRow(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => trim((string) ($row['titolo'] ?? '')),
            'first_name' => trim((string) ($row['nome'] ?? '')),
            'last_name' => trim((string) ($row['cognome'] ?? '')),
            'name' => trim((string) ($row['name'] ?? '')),
            'tax_code' => trim((string) ($row['codice_fiscale'] ?? '')),
            'birth_date' => $this->normalizeDisplayValue($row['data_nascita'] ?? null),
            'birth_city' => trim((string) ($row['citta_nascita'] ?? '')),
            'birth_province' => trim((string) ($row['provincia_nascita'] ?? '')),
            'birth_country' => trim((string) ($row['stato_nascita'] ?? '')),
            'address' => trim((string) ($row['indirizzo_residenza'] ?? '')),
            'city' => trim((string) ($row['citta_residenza'] ?? '')),
            'province' => trim((string) ($row['provincia_residenza'] ?? '')),
            'postal_code' => trim((string) ($row['cap_residenza'] ?? '')),
            'country' => trim((string) ($row['stato_residenza'] ?? '')),
            'gender' => trim((string) ($row['sesso'] ?? '')),
            'phone' => trim((string) ($row['telefono_1'] ?? '')),
            'phone_alt' => trim((string) ($row['telefono_2'] ?? '')),
            'email' => trim((string) ($row['email_1'] ?? '')),
            'email_alt' => trim((string) ($row['email_2'] ?? '')),
            'pec' => trim((string) ($row['pec'] ?? '')),
            'vat_number' => trim((string) ($row['piva'] ?? '')),
            'invoice_code' => trim((string) ($row['codice_univoco_fatturazione'] ?? '')),
            'status' => trim((string) ($row['status'] ?? 'Attivo')),
            'account_expiry_date' => $this->normalizeDisplayValue($row['data_scadenza_account'] ?? null, true),
            'image_path' => trim((string) ($row['immagine_atleta'] ?? '')),
            'image_url' => $this->toPublicUrl(trim((string) ($row['immagine_atleta'] ?? ''))),
            'notes' => trim((string) ($row['note_atleta'] ?? '')),
            'height' => $row['altezza'] !== null ? (string) $row['altezza'] : '',
            'weight' => $row['peso'] !== null ? (string) $row['peso'] : '',
            'size' => trim((string) ($row['misura'] ?? '')),
            'shirt_size' => trim((string) ($row['misura_maglia'] ?? '')),
            'pants_size' => trim((string) ($row['misura_pantaloni'] ?? '')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readDocumentiAtleta(int $idAtleta): array
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                d.iddocumento AS id,
                d.idtipo_documento AS type_id,
                COALESCE(td.tipo_documento, \'\') AS type_name,
                COALESCE(d.descrizione_documento, \'\') AS description,
                d.data_documento AS document_date,
                d.data_scadenza AS expiry_date,
                COALESCE(d.url_documento, \'\') AS url
             FROM documenti d
             INNER JOIN tipi_documento td ON td.idtipo_documento = d.idtipo_documento
             WHERE d.idatleta = :idatleta
             ORDER BY d.data_scadenza DESC, d.iddocumento DESC'
        );
        $stmt->execute(['idatleta' => $idAtleta]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(function (array $row): array {
            $url = trim((string) ($row['url'] ?? ''));
            $row['public_url'] = $this->toPublicUrl($url);
            $row['file_name'] = $url !== '' ? basename($url) : '';

            return $row;
        }, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readIscrizioniAtleta(int $idAtleta): array
    {
        $this->assertLatestDatabaseSchema();
        $iscrizioniColumns = $this->resolveIscrizioniColumnNames();

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                i.idiscrizione AS id,
                i.idiscrizione AS enrollment_id,
                MIN(ihc.idcorso) AS course_id,
                GROUP_CONCAT(CAST(ihc.idcorso AS CHAR) ORDER BY ihc.idcorso SEPARATOR ",") AS course_ids_csv,
                MIN(ihc.data_iscrizione_corso) AS course_enrollment_date,
                 i.abbonamento AS subscription_months,
                     i.' . $iscrizioniColumns['start'] . ' AS start_date,
                     i.' . $iscrizioniColumns['end'] . ' AS end_date,
                     i.' . $iscrizioniColumns['total'] . ' AS total,
                i.stato_iscrizione AS status_code,
                COALESCE(i.note_iscrizione, MAX(ihc.note), \'\') AS notes,
                COALESCE(GROUP_CONCAT(COALESCE(c.nome_corso, \'\') ORDER BY ihc.idcorso SEPARATOR ", "), \'\') AS courses
             FROM iscrizioni_has_corsi ihc
             INNER JOIN iscrizioni i ON i.idiscrizione = ihc.idiscrizione
             LEFT JOIN corsi c ON c.idcorso = ihc.idcorso
             WHERE i.idatleta = :idatleta
                 GROUP BY i.idiscrizione, i.abbonamento, i.' . $iscrizioniColumns['start'] . ', i.' . $iscrizioniColumns['end'] . ', i.' . $iscrizioniColumns['total'] . ', i.stato_iscrizione, i.note_iscrizione
                 ORDER BY i.' . $iscrizioniColumns['start'] . ' DESC, i.idiscrizione DESC'
        );
        $stmt->execute(['idatleta' => $idAtleta]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(function (array $row): array {
            $row['status_label'] = match ((string) ($row['status_code'] ?? '')) {
                'A' => 'Attivo',
                'S' => 'Sospeso',
                'C' => 'Concluso',
                default => '',
            };

            return $row;
        }, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readPagamentiAtleta(int $idAtleta): array
    {
        $this->assertLatestDatabaseSchema();
        $iscrizioniColumns = $this->resolveIscrizioniColumnNames();

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                p.idpagamento AS id,
                ihc_primary.idcorso AS course_id,
                p.idiscrizione AS enrollment_id,
                COALESCE(c.nome_corso, \'\') AS course_name,
                p.data_pagamento AS payment_date,
                p.data_scadenza AS expiry_date,
                p.quota AS amount,
                COALESCE(p.note_pagamento, \'\') AS notes,
                     i.' . $iscrizioniColumns['start'] . ' AS enrollment_start_date,
                     i.' . $iscrizioniColumns['end'] . ' AS enrollment_end_date
             FROM pagamenti p
             INNER JOIN iscrizioni i ON i.idiscrizione = p.idiscrizione
             LEFT JOIN (
                SELECT idiscrizione, MIN(idcorso) AS idcorso
                FROM iscrizioni_has_corsi
                GROUP BY idiscrizione
             ) ihc_primary ON ihc_primary.idiscrizione = p.idiscrizione
             LEFT JOIN corsi c ON c.idcorso = ihc_primary.idcorso
             WHERE i.idatleta = :idatleta
             ORDER BY p.data_pagamento DESC, p.idpagamento DESC'
        );
        $stmt->execute(['idatleta' => $idAtleta]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    private function tableExists(string $tableName): bool
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute(['table_name' => $tableName]);

        return $stmt->fetchColumn() !== false;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $tableName . '` LIKE :column_name');
        $stmt->execute(['column_name' => $columnName]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Supporta sia naming schema nuovo che legacy per la tabella iscrizioni.
     *
     * @return array{start:string,end:string,total:string}
     */
    private function resolveIscrizioniColumnNames(): array
    {
        static $resolved = null;

        if (is_array($resolved)) {
            return $resolved;
        }

        $startColumn = $this->columnExists('iscrizioni', 'data_inizio_iscrizione')
            ? 'data_inizio_iscrizione'
            : ($this->columnExists('iscrizioni', 'data_iscrizione') ? 'data_iscrizione' : null);
        $endColumn = $this->columnExists('iscrizioni', 'data_fine_iscrizione')
            ? 'data_fine_iscrizione'
            : ($this->columnExists('iscrizioni', 'data_scadenza_iscrizione') ? 'data_scadenza_iscrizione' : null);
        $totalColumn = $this->columnExists('iscrizioni', 'totale_abbonamento')
            ? 'totale_abbonamento'
            : ($this->columnExists('iscrizioni', 'totale_iscrizione')
                ? 'totale_iscrizione'
                : ($this->columnExists('iscrizioni', 'quota') ? 'quota' : null));

        if ($startColumn === null) {
            throw new \RuntimeException('Schema DB non allineato alla versione corrente (' . self::DB_SCHEMA_REFERENCE . '): colonna mancante `iscrizioni.data_inizio_iscrizione` (oppure `iscrizioni.data_iscrizione`).');
        }
        if ($endColumn === null) {
            throw new \RuntimeException('Schema DB non allineato alla versione corrente (' . self::DB_SCHEMA_REFERENCE . '): colonna mancante `iscrizioni.data_fine_iscrizione` (oppure `iscrizioni.data_scadenza_iscrizione`).');
        }
        if ($totalColumn === null) {
            throw new \RuntimeException('Schema DB non allineato alla versione corrente (' . self::DB_SCHEMA_REFERENCE . '): colonna mancante `iscrizioni.totale_abbonamento` (oppure `iscrizioni.totale_iscrizione` / `iscrizioni.quota`).');
        }

        $resolved = [
            'start' => $startColumn,
            'end' => $endColumn,
            'total' => $totalColumn,
        ];

        return $resolved;
    }

    private function assertLatestDatabaseSchema(): void
    {
        static $checked = false;

        if ($checked) {
            return;
        }

        $required = [
            'iscrizioni' => [
                'idiscrizione',
                'idatleta',
                'abbonamento',
                'stato_iscrizione',
                'note_iscrizione',
            ],
            'iscrizioni_has_corsi' => [
                'idiscrizione',
                'idcorso',
                'data_iscrizione_corso',
                'note',
            ],
            'pagamenti' => [
                'idpagamento',
                'idiscrizione',
                'data_pagamento',
                'data_scadenza',
                'quota',
                'note_pagamento',
            ],
        ];

        foreach ($required as $table => $columns) {
            if (!$this->tableExists($table)) {
                throw new \RuntimeException('Schema DB non allineato alla versione corrente (' . self::DB_SCHEMA_REFERENCE . '): tabella mancante `' . $table . '`.');
            }

            foreach ($columns as $column) {
                if (!$this->columnExists($table, $column)) {
                    throw new \RuntimeException('Schema DB non allineato alla versione corrente (' . self::DB_SCHEMA_REFERENCE . '): colonna mancante `' . $table . '.' . $column . '`.');
                }
            }
        }

        $this->resolveIscrizioniColumnNames();

        $checked = true;
    }

    private function findIscrizioneIdByAtletaAndCorso(int $idAtleta, int $idCorso): ?int
    {
        if ($idAtleta <= 0 || $idCorso <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT ihc.idiscrizione
             FROM iscrizioni_has_corsi ihc
             INNER JOIN iscrizioni i ON i.idiscrizione = ihc.idiscrizione
             WHERE i.idatleta = :idatleta
               AND ihc.idcorso = :idcorso
             LIMIT 1'
        );
        $stmt->execute([
            'idatleta' => $idAtleta,
            'idcorso' => $idCorso,
        ]);

        $value = $stmt->fetchColumn();

        return $value === false ? null : (int) $value;
    }

    private function countPagamentiByIscrizione(int $idIscrizione): int
    {
        if ($idIscrizione <= 0) {
            return 0;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM pagamenti WHERE idiscrizione = :idiscrizione');
        $stmt->execute(['idiscrizione' => $idIscrizione]);

        return (int) $stmt->fetchColumn();
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : $stringValue;
    }

    private function normalizeNullableDate(mixed $value): ?string
    {
        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : substr($stringValue, 0, 10);
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : (int) $stringValue;
    }

    private function normalizeNullableFloat(mixed $value): ?float
    {
        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : (float) str_replace(',', '.', $stringValue);
    }

    private function normalizeDisplayValue(mixed $value, bool $withTime = false): string
    {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        return $withTime ? substr($value, 0, 10) : $value;
    }

    private function statusToBool(string $status): int
    {
        return $status === 'Sospeso' ? 0 : 1;
    }
}
