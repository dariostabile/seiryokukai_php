<?php

declare(strict_types=1);

namespace App\Services;

final class UtentiService extends BaseService
{
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

    public function readActiveInstructors(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT DISTINCT
                u.idutente AS id,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS name
             FROM utenti u
             INNER JOIN utenti_has_profili up ON up.idutente = u.idutente
             INNER JOIN profili p ON p.idprofilo = up.idprofilo
             WHERE u.cancellato = 0
               AND u.attivo = 1
               AND p.profilo = 'Istruttore'
             ORDER BY name ASC, u.idutente ASC"
        );

        $rows = $stmt->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return is_array($rows) ? $rows : [];
    }

    public function readUsersPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        $pdo = db_connection();

        $start = max(0, $start);
        $length = $length > 0 ? $length : 10;

        $allowedOrder = [
            'id' => 'u.idutente',
            'name' => "TRIM(CONCAT(COALESCE(u.cognome, ''), ' ', COALESCE(u.nome, '')))",
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
}
