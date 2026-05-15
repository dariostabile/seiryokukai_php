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

    public function readClientsPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
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
                COALESCE(telefono_1, '') AS phone
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

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
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
                     COALESCE(u.nome, '') AS first_name,
                     COALESCE(u.cognome, '') AS last_name,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS name,
                COALESCE(u.username, '') AS username,
                COALESCE(u.telefono1, '') AS phone1,
                COALESCE(u.telefono2, '') AS phone2,
                COALESCE(u.email1, '') AS email,
                COALESCE(u.email2, '') AS email2,
                CASE WHEN u.attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                 COALESCE(u.immagine_utente, '') AS image_path,
                 COALESCE(up1.primary_profile_id, 0) AS profile_id,
                 COALESCE(up1.profile_ids_csv, '') AS profile_ids_csv,
                 COALESCE(up1.profile_names_csv, '') AS role,
                 COALESCE(ua1.application_ids_csv, '') AS application_ids_csv,
                 COALESCE(u.data_scadenza_account, '') AS data_scadenza_account
             FROM utenti u
                 LEFT JOIN (
                     SELECT
                         up.idutente,
                         MIN(up.idprofilo) AS primary_profile_id,
                         GROUP_CONCAT(up.idprofilo ORDER BY up.idprofilo ASC) AS profile_ids_csv,
                         GROUP_CONCAT(DISTINCT NULLIF(TRIM(COALESCE(p.profilo, '')), '') ORDER BY p.idprofilo ASC SEPARATOR ', ') AS profile_names_csv
                     FROM utenti_has_profili up
                     LEFT JOIN profili p ON p.idprofilo = up.idprofilo
                     GROUP BY up.idutente
                 ) up1 ON up1.idutente = u.idutente
                 LEFT JOIN (
                     SELECT idutente, GROUP_CONCAT(idapplicazione ORDER BY idapplicazione ASC) AS application_ids_csv
                     FROM utenti_has_applicazioni
                     GROUP BY idutente
                 ) ua1 ON ua1.idutente = u.idutente
             WHERE u.cancellato = 0
             ORDER BY u.idutente DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (is_array($rows)) {
            foreach ($rows as &$row) {
                $row['image_url'] = $this->toPublicUrl((string) ($row['image_path'] ?? ''));
                $row['profile_ids'] = $this->parseIdCsv((string) ($row['profile_ids_csv'] ?? ''));
                $row['application_ids'] = $this->parseIdCsv((string) ($row['application_ids_csv'] ?? ''));
                unset($row['profile_ids_csv']);
                unset($row['application_ids_csv']);
            }
            unset($row);
        }

        return is_array($rows) ? $rows : [];
    }

    public function readUsersPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        $pdo = db_connection();

        $start = max(0, $start);
        $length = $length > 0 ? $length : 10;

        $allowedOrder = [
            'id' => 'u.idutente',
            'name' => "TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, '')))",
            'username' => 'u.username',
            'email' => 'u.email1',
            'role' => 'up1.profile_names_csv',
            'status' => 'u.attivo',
        ];

        $orderSql = $allowedOrder[$orderColumn] ?? 'u.idutente';
        $orderDirSql = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $total = (int) $pdo->query('SELECT COUNT(*) FROM utenti WHERE cancellato = 0')->fetchColumn();

        $whereSql = 'WHERE u.cancellato = 0';
        $params = [];

        if ($search !== '') {
            $whereSql .= ' AND (
                u.nome LIKE :search
                OR u.cognome LIKE :search
                OR u.username LIKE :search
                OR u.email1 LIKE :search
                OR up1.profile_names_csv LIKE :search
            )';
            $params['search'] = '%' . $search . '%';
        }

        $countSql =
            "SELECT COUNT(*)
             FROM utenti u
             LEFT JOIN (
                SELECT
                    up.idutente,
                    MIN(up.idprofilo) AS primary_profile_id,
                    GROUP_CONCAT(up.idprofilo ORDER BY up.idprofilo ASC) AS profile_ids_csv,
                    GROUP_CONCAT(DISTINCT NULLIF(TRIM(COALESCE(p.profilo, '')), '') ORDER BY p.idprofilo ASC SEPARATOR ', ') AS profile_names_csv
                FROM utenti_has_profili up
                LEFT JOIN profili p ON p.idprofilo = up.idprofilo
                GROUP BY up.idutente
             ) up1 ON up1.idutente = u.idutente
             $whereSql";

        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $filtered = (int) $countStmt->fetchColumn();

        $sql =
            "SELECT
                u.idutente AS id,
                COALESCE(u.nome, '') AS first_name,
                COALESCE(u.cognome, '') AS last_name,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS name,
                COALESCE(u.username, '') AS username,
                 COALESCE(u.telefono1, '') AS phone1,
                 COALESCE(u.telefono2, '') AS phone2,
                COALESCE(u.email1, '') AS email,
                 COALESCE(u.email2, '') AS email2,
                CASE WHEN u.attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                     COALESCE(u.immagine_utente, '') AS image_path,
                COALESCE(up1.primary_profile_id, 0) AS profile_id,
                     COALESCE(up1.profile_ids_csv, '') AS profile_ids_csv,
                     COALESCE(up1.profile_names_csv, '') AS role,
                     COALESCE(ua1.application_ids_csv, '') AS application_ids_csv,
                     COALESCE(u.data_scadenza_account, '') AS data_scadenza_account
             FROM utenti u
             LEFT JOIN (
                SELECT
                    up.idutente,
                    MIN(up.idprofilo) AS primary_profile_id,
                    GROUP_CONCAT(up.idprofilo ORDER BY up.idprofilo ASC) AS profile_ids_csv,
                    GROUP_CONCAT(DISTINCT NULLIF(TRIM(COALESCE(p.profilo, '')), '') ORDER BY p.idprofilo ASC SEPARATOR ', ') AS profile_names_csv
                FROM utenti_has_profili up
                LEFT JOIN profili p ON p.idprofilo = up.idprofilo
                GROUP BY up.idutente
             ) up1 ON up1.idutente = u.idutente
                 LEFT JOIN (
                     SELECT idutente, GROUP_CONCAT(idapplicazione ORDER BY idapplicazione ASC) AS application_ids_csv
                     FROM utenti_has_applicazioni
                     GROUP BY idutente
                 ) ua1 ON ua1.idutente = u.idutente
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
                $row['profile_ids'] = $this->parseIdCsv((string) ($row['profile_ids_csv'] ?? ''));
                $row['application_ids'] = $this->parseIdCsv((string) ($row['application_ids_csv'] ?? ''));
                unset($row['profile_ids_csv']);
                unset($row['application_ids_csv']);
            }
            unset($row);
        }

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
    }

    public function findUserById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                u.idutente AS id,
                COALESCE(u.nome, '') AS first_name,
                COALESCE(u.cognome, '') AS last_name,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS name,
                COALESCE(u.username, '') AS username,
                 COALESCE(u.telefono1, '') AS phone1,
                 COALESCE(u.telefono2, '') AS phone2,
                COALESCE(u.email1, '') AS email,
                 COALESCE(u.email2, '') AS email2,
                CASE WHEN u.attivo = 1 THEN 'Attivo' ELSE 'Sospeso' END AS status,
                COALESCE(u.immagine_utente, '') AS image_path,
                COALESCE(up1.primary_profile_id, 0) AS profile_id,
                     COALESCE(up1.profile_ids_csv, '') AS profile_ids_csv,
                     COALESCE(up1.profile_names_csv, '') AS role,
                     COALESCE(ua1.application_ids_csv, '') AS application_ids_csv,
                     COALESCE(u.data_scadenza_account, '') AS data_scadenza_account
             FROM utenti u
             LEFT JOIN (
                SELECT
                    up.idutente,
                    MIN(up.idprofilo) AS primary_profile_id,
                    GROUP_CONCAT(up.idprofilo ORDER BY up.idprofilo ASC) AS profile_ids_csv,
                    GROUP_CONCAT(DISTINCT NULLIF(TRIM(COALESCE(p.profilo, '')), '') ORDER BY p.idprofilo ASC SEPARATOR ', ') AS profile_names_csv
                FROM utenti_has_profili up
                LEFT JOIN profili p ON p.idprofilo = up.idprofilo
                GROUP BY up.idutente
             ) up1 ON up1.idutente = u.idutente
                 LEFT JOIN (
                     SELECT idutente, GROUP_CONCAT(idapplicazione ORDER BY idapplicazione ASC) AS application_ids_csv
                     FROM utenti_has_applicazioni
                     GROUP BY idutente
                 ) ua1 ON ua1.idutente = u.idutente
             WHERE u.idutente = :idutente
               AND u.cancellato = 0
             LIMIT 1"
        );
        $stmt->execute(['idutente' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        $row['image_url'] = $this->toPublicUrl((string) ($row['image_path'] ?? ''));
        $row['profile_ids'] = $this->parseIdCsv((string) ($row['profile_ids_csv'] ?? ''));
        $row['application_ids'] = $this->parseIdCsv((string) ($row['application_ids_csv'] ?? ''));
        unset($row['profile_ids_csv']);
        unset($row['application_ids_csv']);

        return $row;
    }

    public function readApplicationsCatalog(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                a.idapplicazione AS id,
                COALESCE(a.applicazione, '') AS name,
                COALESCE(a.url_applicazione, '') AS url,
                COALESCE(a.icona_applicazione, '') AS icon,
                COALESCE(a.ordine_applicazione, 0) AS app_order,
                COALESCE(ga.idgruppo_applicazioni, 0) AS group_id,
                COALESCE(ga.gruppo_applicazioni, 'Applicazioni') AS group_name,
                COALESCE(ga.icona_gruppo, '') AS group_icon,
                COALESCE(ga.ordine_gruppo, 0) AS group_order
             FROM applicazioni a
             INNER JOIN gruppi_applicazioni ga ON ga.idgruppo_applicazioni = a.idgruppo_applicazioni
             ORDER BY ga.ordine_gruppo ASC, a.ordine_applicazione ASC, a.idapplicazione ASC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function readProfiles(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idprofilo AS id,
                COALESCE(profilo, '') AS name
             FROM profili
             ORDER BY ordine_profilo ASC, idprofilo ASC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addUser(
        string $nome,
        string $cognome,
        string $username,
        string $password,
        string $email = '',
        string $phone1 = '',
        string $phone2 = '',
        string $email2 = '',
        array $profileIds = [],
        bool $attivo = true,
        string $accountExpiryDate = '',
        array $applicationIds = []
    ): array {
        $nome = trim($nome);
        $cognome = trim($cognome);
        $username = trim($username);
        $email = trim($email);
        $phone1 = trim($phone1);
        $phone2 = trim($phone2);
        $email2 = trim($email2);
        $accountExpiryDate = trim($accountExpiryDate);
        if ($accountExpiryDate === '') {
            $accountExpiryDate = date('Y-m-d', strtotime('+1 year'));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $accountExpiryDate)) {
            throw new \InvalidArgumentException('Data scadenza account non valida');
        }
        $accountExpiryDateTime = $accountExpiryDate . ' 23:59:59';
        $profileIds = array_values(array_unique(array_filter(array_map('intval', $profileIds), static fn (int $id): bool => $id > 0)));
        $applicationIds = array_values(array_unique(array_filter(array_map('intval', $applicationIds), static fn (int $id): bool => $id > 0)));

        if ($username === '' || $password === '') {
            throw new \InvalidArgumentException('Username e password sono obbligatori');
        }

        if (mb_strlen($password) < 8) {
            throw new \InvalidArgumentException('La password deve contenere almeno 8 caratteri');
        }

        if (mb_strlen($username) > 45) {
            throw new \InvalidArgumentException('Username troppo lungo');
        }

        $pdo = db_connection();

        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM utenti WHERE username = :username AND cancellato = 0');
        $checkStmt->execute(['username' => $username]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException('Username gia presente');
        }

        $passwordHash = hash('sha256', $password);

        $pdo->beginTransaction();

        try {
            $insertStmt = $pdo->prepare(
                 'INSERT INTO utenti (nome, cognome, username, password, telefono1, telefono2, email1, email2, attivo, cancellato, superadmin, data_creazione_account, data_scadenza_account)
                  VALUES (:nome, :cognome, :username, :password, :telefono1, :telefono2, :email1, :email2, :attivo, 0, 0, NOW(), :data_scadenza_account)'
            );
            $insertStmt->execute([
                'nome' => $nome !== '' ? $nome : null,
                'cognome' => $cognome !== '' ? $cognome : null,
                'username' => $username,
                'password' => $passwordHash,
                'telefono1' => $phone1 !== '' ? $phone1 : null,
                'telefono2' => $phone2 !== '' ? $phone2 : null,
                'email1' => $email !== '' ? $email : null,
                'email2' => $email2 !== '' ? $email2 : null,
                'attivo' => $attivo ? 1 : 0,
                'data_scadenza_account' => $accountExpiryDateTime,
            ]);

            $newUserId = (int) $pdo->lastInsertId();

            if ($profileIds !== []) {
                $profileStmt = $pdo->prepare('INSERT INTO utenti_has_profili (idutente, idprofilo) VALUES (:idutente, :idprofilo)');
                foreach ($profileIds as $profileId) {
                    $profileStmt->execute([
                        'idutente' => $newUserId,
                        'idprofilo' => $profileId,
                    ]);
                }
            }

            if ($applicationIds !== []) {
                $applicationStmt = $pdo->prepare('INSERT INTO utenti_has_applicazioni (idutente, idapplicazione) VALUES (:idutente, :idapplicazione)');
                foreach ($applicationIds as $applicationId) {
                    $applicationStmt->execute([
                        'idutente' => $newUserId,
                        'idapplicazione' => $applicationId,
                    ]);
                }
            }

            $pdo->commit();

            return [
                'id' => $newUserId,
                'name' => trim($nome . ' ' . $cognome),
                'username' => $username,
                'email' => $email,
                'phone1' => $phone1,
                'phone2' => $phone2,
                'email2' => $email2,
                'data_scadenza_account' => $accountExpiryDate,
                'status' => $attivo ? 'Attivo' : 'Sospeso',
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
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

    public function updateUserImage(int $id, ?string $imagePath): bool
    {
        if ($id <= 0) {
            return false;
        }

        $cleanPath = $imagePath !== null ? trim($imagePath) : null;
        if ($cleanPath === '') {
            $cleanPath = null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE utenti SET immagine_utente = :immagine_utente WHERE idutente = :idutente AND cancellato = 0');
        $stmt->execute([
            'immagine_utente' => $cleanPath,
            'idutente' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateUser(
        int $id,
        string $nome,
        string $cognome,
        string $username,
        string $email = '',
        string $phone1 = '',
        string $phone2 = '',
        string $email2 = '',
        array $profileIds = [],
        bool $attivo = true,
        ?string $imagePath = null,
        ?string $newPassword = null,
        ?array $applicationIds = null,
        string $accountExpiryDate = ''
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $nome = trim($nome);
        $cognome = trim($cognome);
        $username = trim($username);
        $email = trim($email);
        $phone1 = trim($phone1);
        $phone2 = trim($phone2);
        $email2 = trim($email2);
        $accountExpiryDate = trim($accountExpiryDate);
        $accountExpiryDateTime = null;
        if ($accountExpiryDate !== '') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $accountExpiryDate)) {
                throw new \InvalidArgumentException('Data scadenza account non valida');
            }
            $accountExpiryDateTime = $accountExpiryDate . ' 23:59:59';
        }
        $profileIds = array_values(array_unique(array_filter(array_map('intval', $profileIds), static fn (int $id): bool => $id > 0)));
        $newPassword = trim((string) $newPassword);
        $applicationIds = $applicationIds !== null
            ? array_values(array_unique(array_map('intval', $applicationIds)))
            : null;

        if ($username === '') {
            throw new \InvalidArgumentException('Username obbligatorio');
        }

        if (mb_strlen($username) > 45) {
            throw new \InvalidArgumentException('Username troppo lungo');
        }

        if ($newPassword !== '' && mb_strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('La password deve contenere almeno 8 caratteri');
        }

        $pdo = db_connection();

        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM utenti WHERE username = :username AND idutente <> :idutente AND cancellato = 0');
        $checkStmt->execute([
            'username' => $username,
            'idutente' => $id,
        ]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException('Username gia presente');
        }

        $pdo->beginTransaction();

        try {
            $setParts = [
                'nome = :nome',
                'cognome = :cognome',
                'username = :username',
                'telefono1 = :telefono1',
                'telefono2 = :telefono2',
                'email1 = :email1',
                'email2 = :email2',
                'data_scadenza_account = :data_scadenza_account',
                'immagine_utente = :immagine_utente',
                'attivo = :attivo',
            ];

            $params = [
                'nome' => $nome !== '' ? $nome : null,
                'cognome' => $cognome !== '' ? $cognome : null,
                'username' => $username,
                'telefono1' => $phone1 !== '' ? $phone1 : null,
                'telefono2' => $phone2 !== '' ? $phone2 : null,
                'email1' => $email !== '' ? $email : null,
                'email2' => $email2 !== '' ? $email2 : null,
                'data_scadenza_account' => $accountExpiryDateTime,
                'immagine_utente' => $imagePath !== null && trim($imagePath) !== '' ? trim($imagePath) : null,
                'attivo' => $attivo ? 1 : 0,
                'idutente' => $id,
            ];

            if ($newPassword !== '') {
                $setParts[] = 'password = :password';
                $setParts[] = 'data_cambio_password = NOW()';
                $params['password'] = hash('sha256', $newPassword);
            }

            $updateSql =
                'UPDATE utenti
                 SET ' . implode(",\n                     ", $setParts) . '
                 WHERE idutente = :idutente
                   AND cancellato = 0';

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute($params);

            $deleteProfileStmt = $pdo->prepare('DELETE FROM utenti_has_profili WHERE idutente = :idutente');
            $deleteProfileStmt->execute(['idutente' => $id]);

            if ($profileIds !== []) {
                $insertProfileStmt = $pdo->prepare('INSERT INTO utenti_has_profili (idutente, idprofilo) VALUES (:idutente, :idprofilo)');
                foreach ($profileIds as $profileId) {
                    $insertProfileStmt->execute([
                        'idutente' => $id,
                        'idprofilo' => $profileId,
                    ]);
                }
            }

            if ($applicationIds !== null) {
                $deleteAppsStmt = $pdo->prepare('DELETE FROM utenti_has_applicazioni WHERE idutente = :idutente');
                $deleteAppsStmt->execute(['idutente' => $id]);

                if ($applicationIds !== []) {
                    $insertAppStmt = $pdo->prepare('INSERT INTO utenti_has_applicazioni (idutente, idapplicazione) VALUES (:idutente, :idapplicazione)');
                    foreach ($applicationIds as $applicationId) {
                        if ($applicationId <= 0) {
                            continue;
                        }
                        $insertAppStmt->execute([
                            'idutente' => $id,
                            'idapplicazione' => $applicationId,
                        ]);
                    }
                }
            }

            $pdo->commit();

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function deleteUser(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE utenti SET cancellato = 1, attivo = 0 WHERE idutente = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return int[]
     */
    private function parseIdCsv(string $csv): array
    {
        $csv = trim($csv);
        if ($csv === '') {
            return [];
        }

        $parts = explode(',', $csv);
        $ids = [];
        foreach ($parts as $part) {
            $id = (int) trim($part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
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

    public function readCoursesPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        $pdo = db_connection();

        $start = max(0, $start);
        $length = $length > 0 ? $length : 10;

        $allowedOrder = [
            'id' => 'c.idcorso',
            'name' => 'c.nome_corso',
            'site' => 's.sede',
            'discipline' => 'd.disciplina',
            'teacher' => "TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, '')))",
            'start_date' => 'c.data_inizio_corso',
            'monthly_fee' => 'c.quota_mensile_corso',
        ];

        $orderSql = $allowedOrder[$orderColumn] ?? 'c.idcorso';
        $orderDirSql = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $total = (int) $pdo->query('SELECT COUNT(*) FROM corsi')->fetchColumn();

        $whereSql = '';
        $params = [];

        if ($search !== '') {
            $whereSql = 'WHERE (
                c.nome_corso LIKE :search
                OR s.sede LIKE :search
                OR d.disciplina LIKE :search
                OR u.nome LIKE :search
                OR u.cognome LIKE :search
            )';
            $params['search'] = '%' . $search . '%';
        }

        $countSql =
            "SELECT COUNT(*)
             FROM corsi c
             INNER JOIN sedi s ON s.idsede = c.idsede
             INNER JOIN discipline d ON d.iddisciplina = c.iddisciplina
             INNER JOIN utenti u ON u.idutente = c.idutente
             $whereSql";

        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $filtered = (int) $countStmt->fetchColumn();

        $sql =
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

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
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

    private function toPublicUrl(string $path): string
    {
        $cleanPath = ltrim(trim($path), '/');
        if ($cleanPath === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $cleanPath) === 1) {
            return $cleanPath;
        }

        return '/seiryokukai_php/' . $cleanPath;
    }
}
