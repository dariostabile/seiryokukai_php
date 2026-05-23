<?php

declare(strict_types=1);

namespace App\Services;

final class AtletiService extends BaseService
{
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
        $pdo = db_connection();
        $courseIds = array_values(array_filter(array_map('intval', (array) ($payload['course_ids'] ?? [])), static fn (int $value): bool => $value > 0));
        if ($courseIds === []) {
            throw new \InvalidArgumentException('Seleziona almeno un corso per l\'iscrizione');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO atleti_has_corsi (
                idatleta,
                idcorso,
                data_iscrizione,
                data_scadenza_iscrizione,
                quota,
                stato_iscrizione,
                note_iscrizione
            ) VALUES (
                :idatleta,
                :idcorso,
                :data_iscrizione,
                :data_scadenza_iscrizione,
                :quota,
                :stato_iscrizione,
                :note_iscrizione
            )
            ON DUPLICATE KEY UPDATE
                data_iscrizione = VALUES(data_iscrizione),
                data_scadenza_iscrizione = VALUES(data_scadenza_iscrizione),
                quota = VALUES(quota),
                stato_iscrizione = VALUES(stato_iscrizione),
                note_iscrizione = VALUES(note_iscrizione)'
        );

        foreach ($courseIds as $courseId) {
            $stmt->execute([
                'idatleta' => $idAtleta,
                'idcorso' => $courseId,
                'data_iscrizione' => $this->normalizeNullableDate($payload['data_inizio_iscrizione'] ?? null),
                'data_scadenza_iscrizione' => $this->normalizeNullableDate($payload['data_fine_iscrizione'] ?? null),
                'quota' => $this->normalizeNullableFloat($payload['totale_iscrizione'] ?? null),
                'stato_iscrizione' => $this->normalizeNullableString($payload['stato_iscrizione'] ?? null),
                'note_iscrizione' => $this->normalizeNullableString($payload['note_iscrizione'] ?? null),
            ]);
        }

        return count($courseIds);
    }

    public function addPagamentoAtleta(int $idAtleta, array $payload): int
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO pagamenti (
                idatleta,
                idcorso,
                data_pagamento,
                data_scadenza,
                quota_pagamento,
                note_pagamento
            ) VALUES (
                :idatleta,
                :idcorso,
                :data_pagamento,
                :data_scadenza,
                :quota_pagamento,
                :note_pagamento
            )'
        );
        $stmt->execute([
            'idatleta' => $idAtleta,
            'idcorso' => (int) ($payload['idcorso'] ?? 0),
            'data_pagamento' => $this->normalizeNullableDate($payload['data_pagamento'] ?? null),
            'data_scadenza' => $this->normalizeNullableDate($payload['data_scadenza'] ?? null),
            'quota_pagamento' => $this->normalizeNullableFloat($payload['quota_pagamento'] ?? null),
            'note_pagamento' => $this->normalizeNullableString($payload['note_pagamento'] ?? null),
        ]);

        return (int) $pdo->lastInsertId();
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
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                ahc.idcorso AS id,
                ahc.idcorso AS course_id,
                ahc.data_iscrizione AS start_date,
                ahc.data_scadenza_iscrizione AS end_date,
                ahc.quota AS total,
                ahc.stato_iscrizione AS status_code,
                COALESCE(ahc.note_iscrizione, \'\') AS notes,
                COALESCE(c.nome_corso, \'\') AS courses
             FROM atleti_has_corsi ahc
             LEFT JOIN corsi c ON c.idcorso = ahc.idcorso
             WHERE ahc.idatleta = :idatleta
             ORDER BY ahc.data_iscrizione DESC, ahc.idcorso DESC'
        );
        $stmt->execute(['idatleta' => $idAtleta]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(function (array $row): array {
            $row['status_label'] = match ((string) ($row['status_code'] ?? '')) {
                'A' => 'Attiva',
                'S' => 'Sospesa',
                'C' => 'Conclusa',
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
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                p.idpagamento AS id,
                p.idcorso AS course_id,
                p.idcorso AS enrollment_id,
                COALESCE(c.nome_corso, \'\') AS course_name,
                p.data_pagamento AS payment_date,
                p.data_scadenza AS expiry_date,
                p.quota_pagamento AS amount,
                COALESCE(p.note_pagamento, \'\') AS notes,
                ahc.data_iscrizione AS enrollment_start_date,
                ahc.data_scadenza_iscrizione AS enrollment_end_date
             FROM pagamenti p
             LEFT JOIN corsi c ON c.idcorso = p.idcorso
             LEFT JOIN atleti_has_corsi ahc ON ahc.idatleta = p.idatleta AND ahc.idcorso = p.idcorso
             WHERE p.idatleta = :idatleta
             ORDER BY p.data_pagamento DESC, p.idpagamento DESC'
        );
        $stmt->execute(['idatleta' => $idAtleta]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
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
