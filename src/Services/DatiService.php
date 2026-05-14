<?php

declare(strict_types=1);

namespace App\Services;

final class DatiService
{
    public function readClients(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idatleta AS id,
                TRIM(CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, ''))) AS name,
                CASE WHEN attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(email_1, '') AS email,
                COALESCE(telefono_1, '') AS phone
             FROM atleti
             WHERE cancellato = 0
             ORDER BY idatleta DESC"
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function dashboardStats(): array
    {
        $pdo = db_connection();

        $totalClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0')->fetchColumn();
        $activeClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0 AND attivo = 1')->fetchColumn();
        $pausedClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0 AND attivo = 0')->fetchColumn();
        $todayAttendance = (int) $pdo->query('SELECT COUNT(*) FROM presenze WHERE data_corso = CURDATE() AND presente = 1')->fetchColumn();

        return [
            'totalClients' => $totalClients,
            'activeClients' => $activeClients,
            'pausedClients' => $pausedClients,
            'todayAttendance' => $todayAttendance,
        ];
    }

    public function addClient(string $name, string $plan = ''): array
    {
        $name = trim($name);
        $parts = preg_split('/\s+/', $name) ?: [];
        $nome = (string) array_shift($parts);
        $cognome = trim(implode(' ', $parts));

        if ($nome === '') {
            throw new \InvalidArgumentException('Il nome atleta non puo essere vuoto');
        }

        $pdo = db_connection();

        $stmt = $pdo->prepare(
            'INSERT INTO atleti (nome, cognome, attivo, cancellato, data_creazione_account)
             VALUES (:nome, :cognome, 1, 0, NOW())'
        );
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
        ]);

        $id = (int) $pdo->lastInsertId();

        return [
            'id' => $id,
            'name' => trim($nome . ' ' . $cognome),
            'status' => 'Attivo',
            'email' => '',
            'phone' => '',
        ];
    }

    public function findClientById(int $id): ?array
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                idatleta AS id,
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

        return $row;
    }

    public function updateClientStatus(int $id, string $status): bool
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

    public function deleteClient(int $id): bool
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE atleti SET cancellato = 1, attivo = 0 WHERE idatleta = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function readUsers(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                u.idutente AS id,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS name,
                COALESCE(u.username, '') AS username,
                COALESCE(u.email1, '') AS email,
                CASE WHEN u.attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(p.profilo, '') AS role
             FROM utenti u
             LEFT JOIN utenti_has_profili up ON up.idutente = u.idutente
             LEFT JOIN profili p ON p.idprofilo = up.idprofilo
             WHERE u.cancellato = 0
             ORDER BY u.idutente DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function updateUserStatus(int $id, string $status): bool
    {
        $allowedStatuses = ['Attivo', 'Sospeso'];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $activeValue = $status === 'Attivo' ? 1 : 0;
        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE utenti SET attivo = :attivo WHERE idutente = :id AND cancellato = 0');
        $stmt->execute([
            'attivo' => $activeValue,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function readSites(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idsede AS id,
                COALESCE(sede, '') AS name,
                COALESCE(codice_sede, '') AS code
             FROM sedi
             ORDER BY idsede DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addSite(string $name, string $code): array
    {
        $name = trim($name);
        $code = strtoupper(trim($code));

        if ($name === '') {
            throw new \InvalidArgumentException('Il nome sede non puo essere vuoto');
        }

        if ($code === '') {
            $code = preg_replace('/\s+/', '_', strtoupper($name)) ?: 'SEDE';
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('INSERT INTO sedi (sede, codice_sede) VALUES (:sede, :codice_sede)');
        $stmt->execute([
            'sede' => $name,
            'codice_sede' => $code,
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'name' => $name,
            'code' => $code,
        ];
    }

    public function readDocumentTypes(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idtipo_documento AS id,
                COALESCE(tipo_documento, '') AS type
             FROM tipi_documento
             ORDER BY idtipo_documento DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addDocumentType(string $type): array
    {
        $type = trim($type);

        if ($type === '') {
            throw new \InvalidArgumentException('Il tipo documento non puo essere vuoto');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('INSERT INTO tipi_documento (tipo_documento) VALUES (:tipo_documento)');
        $stmt->execute(['tipo_documento' => $type]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'type' => $type,
        ];
    }

    public function readDisciplines(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                iddisciplina AS id,
                COALESCE(disciplina, '') AS name,
                COALESCE(note_disciplina, '') AS notes
             FROM discipline
             ORDER BY iddisciplina DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addDiscipline(string $name, string $notes = ''): array
    {
        $name = trim($name);
        $notes = trim($notes);

        if ($name === '') {
            throw new \InvalidArgumentException('Il nome disciplina non puo essere vuoto');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('INSERT INTO discipline (disciplina, note_disciplina) VALUES (:disciplina, :note_disciplina)');
        $stmt->execute([
            'disciplina' => $name,
            'note_disciplina' => $notes !== '' ? $notes : null,
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'name' => $name,
            'notes' => $notes,
        ];
    }

    public function readCourses(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                c.idcorso AS id,
                COALESCE(c.nome_corso, '') AS name,
                COALESCE(s.sede, '') AS site,
                COALESCE(d.disciplina, '') AS discipline,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS teacher,
                c.data_inizio_corso AS start_date,
                c.quota_mensile_corso AS monthly_fee,
                     c.lun_inizio, c.lun_fine,
                     c.mar_inizio, c.mar_fine,
                     c.mer_inizio, c.mer_fine,
                     c.gio_inizio, c.gio_fine,
                     c.ven_inizio, c.ven_fine,
                     c.sab_inizio, c.sab_fine,
                     c.dom_inizio, c.dom_fine
             FROM corsi c
             INNER JOIN sedi s ON s.idsede = c.idsede
             INNER JOIN discipline d ON d.iddisciplina = c.iddisciplina
             INNER JOIN utenti u ON u.idutente = c.idutente
             ORDER BY c.idcorso DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addCourse(
        int $siteId,
        int $disciplineId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?float $monthlyFee = null,
        array $orari = []
    ): array {
        $name = trim($name);

        if ($siteId <= 0 || $disciplineId <= 0 || $userId <= 0 || $name === '') {
            throw new \InvalidArgumentException('Dati corso non validi');
        }

        $orariNormalizzati = $this->normalizzaOrariSettimana($orari);

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO corsi (
                idsede, iddisciplina, idutente, nome_corso, data_inizio_corso, quota_mensile_corso,
                lun_inizio, lun_fine, mar_inizio, mar_fine, mer_inizio, mer_fine,
                gio_inizio, gio_fine, ven_inizio, ven_fine, sab_inizio, sab_fine,
                dom_inizio, dom_fine
             ) VALUES (
                :idsede, :iddisciplina, :idutente, :nome_corso, :data_inizio_corso, :quota_mensile_corso,
                :lun_inizio, :lun_fine, :mar_inizio, :mar_fine, :mer_inizio, :mer_fine,
                :gio_inizio, :gio_fine, :ven_inizio, :ven_fine, :sab_inizio, :sab_fine,
                :dom_inizio, :dom_fine
             )'
        );
        $stmt->execute([
            'idsede' => $siteId,
            'iddisciplina' => $disciplineId,
            'idutente' => $userId,
            'nome_corso' => $name,
            'data_inizio_corso' => $startDate,
            'quota_mensile_corso' => $monthlyFee,
            'lun_inizio' => $orariNormalizzati['lun_inizio'],
            'lun_fine' => $orariNormalizzati['lun_fine'],
            'mar_inizio' => $orariNormalizzati['mar_inizio'],
            'mar_fine' => $orariNormalizzati['mar_fine'],
            'mer_inizio' => $orariNormalizzati['mer_inizio'],
            'mer_fine' => $orariNormalizzati['mer_fine'],
            'gio_inizio' => $orariNormalizzati['gio_inizio'],
            'gio_fine' => $orariNormalizzati['gio_fine'],
            'ven_inizio' => $orariNormalizzati['ven_inizio'],
            'ven_fine' => $orariNormalizzati['ven_fine'],
            'sab_inizio' => $orariNormalizzati['sab_inizio'],
            'sab_fine' => $orariNormalizzati['sab_fine'],
            'dom_inizio' => $orariNormalizzati['dom_inizio'],
            'dom_fine' => $orariNormalizzati['dom_fine'],
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'name' => $name,
        ];
    }

    public function readCourseById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                c.idcorso AS id,
                c.idsede AS site_id,
                c.iddisciplina AS discipline_id,
                c.idutente AS user_id,
                COALESCE(c.nome_corso, '') AS name,
                COALESCE(s.sede, '') AS site,
                COALESCE(d.disciplina, '') AS discipline,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS teacher,
                c.data_inizio_corso AS start_date,
                c.quota_mensile_corso AS monthly_fee,
                     c.lun_inizio, c.lun_fine,
                     c.mar_inizio, c.mar_fine,
                     c.mer_inizio, c.mer_fine,
                     c.gio_inizio, c.gio_fine,
                     c.ven_inizio, c.ven_fine,
                     c.sab_inizio, c.sab_fine,
                     c.dom_inizio, c.dom_fine
             FROM corsi c
             INNER JOIN sedi s ON s.idsede = c.idsede
             INNER JOIN discipline d ON d.iddisciplina = c.iddisciplina
             INNER JOIN utenti u ON u.idutente = c.idutente
             WHERE c.idcorso = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function updateCourse(
        int $id,
        int $siteId,
        int $disciplineId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?float $monthlyFee = null,
        array $orari = []
    ): bool {
        $name = trim($name);

        if ($id <= 0 || $siteId <= 0 || $disciplineId <= 0 || $userId <= 0 || $name === '') {
            throw new \InvalidArgumentException('Dati corso non validi per aggiornamento');
        }

        $orariNormalizzati = $this->normalizzaOrariSettimana($orari);

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'UPDATE corsi
             SET idsede = :idsede,
                 iddisciplina = :iddisciplina,
                 idutente = :idutente,
                 nome_corso = :nome_corso,
                 data_inizio_corso = :data_inizio_corso,
                 quota_mensile_corso = :quota_mensile_corso,
                 lun_inizio = :lun_inizio,
                 lun_fine = :lun_fine,
                 mar_inizio = :mar_inizio,
                 mar_fine = :mar_fine,
                 mer_inizio = :mer_inizio,
                 mer_fine = :mer_fine,
                 gio_inizio = :gio_inizio,
                 gio_fine = :gio_fine,
                 ven_inizio = :ven_inizio,
                 ven_fine = :ven_fine,
                 sab_inizio = :sab_inizio,
                 sab_fine = :sab_fine,
                 dom_inizio = :dom_inizio,
                 dom_fine = :dom_fine
             WHERE idcorso = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'idsede' => $siteId,
            'iddisciplina' => $disciplineId,
            'idutente' => $userId,
            'nome_corso' => $name,
            'data_inizio_corso' => $startDate,
            'quota_mensile_corso' => $monthlyFee,
            'lun_inizio' => $orariNormalizzati['lun_inizio'],
            'lun_fine' => $orariNormalizzati['lun_fine'],
            'mar_inizio' => $orariNormalizzati['mar_inizio'],
            'mar_fine' => $orariNormalizzati['mar_fine'],
            'mer_inizio' => $orariNormalizzati['mer_inizio'],
            'mer_fine' => $orariNormalizzati['mer_fine'],
            'gio_inizio' => $orariNormalizzati['gio_inizio'],
            'gio_fine' => $orariNormalizzati['gio_fine'],
            'ven_inizio' => $orariNormalizzati['ven_inizio'],
            'ven_fine' => $orariNormalizzati['ven_fine'],
            'sab_inizio' => $orariNormalizzati['sab_inizio'],
            'sab_fine' => $orariNormalizzati['sab_fine'],
            'dom_inizio' => $orariNormalizzati['dom_inizio'],
            'dom_fine' => $orariNormalizzati['dom_fine'],
        ]);
    }

    private function normalizzaOrariSettimana(array $orari): array
    {
        $chiavi = [
            'lun_inizio', 'lun_fine',
            'mar_inizio', 'mar_fine',
            'mer_inizio', 'mer_fine',
            'gio_inizio', 'gio_fine',
            'ven_inizio', 'ven_fine',
            'sab_inizio', 'sab_fine',
            'dom_inizio', 'dom_fine',
        ];

        $out = [];

        foreach ($chiavi as $chiave) {
            $valore = trim((string) ($orari[$chiave] ?? ''));
            if ($valore === '') {
                $out[$chiave] = null;
                continue;
            }

            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valore)) {
                throw new \InvalidArgumentException('Formato orario non valido per ' . $chiave);
            }

            $out[$chiave] = $valore;
        }

        return $out;
    }

    public function deleteCourse(int $id): bool
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID corso non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('DELETE FROM corsi WHERE idcorso = :id');
        return $stmt->execute(['id' => $id]);
    }
}
