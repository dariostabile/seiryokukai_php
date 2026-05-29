<?php

declare(strict_types=1);

namespace App\Services;

final class CorsiService extends BaseService
{
    public function readCorsi(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                c.idcorso AS id,
                COALESCE(c.nome_corso, '') AS name,
                COALESCE(s.sede, '') AS sede,
                COALESCE(d.disciplina, '') AS disciplina,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS teacher,
                c.data_inizio_corso AS start_date,
                 c.data_fine_corso AS end_date,
                c.quota_mensile_corso AS monthly_fee,
                 COALESCE(c.attivo, 1) AS active,
                 COALESCE(c.immagine_corso, '') AS image_path,
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

    public function readCorsiPage(int $start, int $length, string $search, string $orderColumn, string $orderDir, bool $activeOnly = false): array
    {
        $pdo = db_connection();

        $start = max(0, $start);
        $length = $length > 0 ? $length : 10;

        $allowedOrder = [
            'id' => 'c.idcorso',
            'name' => 'c.nome_corso',
            'site' => 's.sede',
            'disciplina' => 'd.disciplina',
            'teacher' => "TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, '')))",
            'start_date' => 'c.data_inizio_corso',
            'end_date' => 'c.data_fine_corso',
            'monthly_fee' => 'c.quota_mensile_corso',
            'active' => 'c.attivo',
        ];

        $orderSql = $allowedOrder[$orderColumn] ?? 'c.idcorso';
        $orderDirSql = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $total = (int) $pdo->query('SELECT COUNT(*) FROM corsi')->fetchColumn();

        $conditions = [];
        $params = [];

        if ($activeOnly) {
            $conditions[] = 'c.attivo = :activeOnly';
            $params['activeOnly'] = 1;
        }

        if ($search !== '') {
            $conditions[] = '(
                c.nome_corso LIKE :search
                OR s.sede LIKE :search
                OR d.disciplina LIKE :search
                OR u.nome LIKE :search
                OR u.cognome LIKE :search
            )';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = '';
        if (count($conditions) > 0) {
            $whereSql = 'WHERE ' . implode(' AND ', $conditions);
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
                c.idsede AS sede_id,
                c.iddisciplina AS disciplina_id,
                c.idutente AS user_id,
                COALESCE(c.nome_corso, '') AS name,
                COALESCE(s.sede, '') AS sede,
                COALESCE(d.disciplina, '') AS disciplina,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS teacher,
                c.data_inizio_corso AS start_date,
                c.data_fine_corso AS end_date,
                c.quota_mensile_corso AS monthly_fee,
                COALESCE(c.attivo, 1) AS active,
                COALESCE(c.immagine_corso, '') AS image_path,
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

        if (is_array($rows)) {
            foreach ($rows as &$row) {
                $row['image_path'] = $this->toPublicUrl((string) ($row['image_path'] ?? ''));
            }
            unset($row);
        }

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
    }

    public function addCorso(
        int $sedeId,
        int $disciplinaId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): array {
        $name = trim($name);
        $active = $active === 0 ? 0 : 1;

        if ($sedeId <= 0 || $disciplinaId <= 0 || $userId <= 0 || $name === '') {
            throw new \InvalidArgumentException('Dati corso non validi');
        }

        $this->assertActiveInstructor($userId);

        $orariNormalizzati = $this->normalizzaOrariSettimana($orari);

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO corsi (
                idsede, iddisciplina, idutente, nome_corso, data_inizio_corso, data_fine_corso, quota_mensile_corso, attivo,
                lun_inizio, lun_fine, mar_inizio, mar_fine, mer_inizio, mer_fine,
                gio_inizio, gio_fine, ven_inizio, ven_fine, sab_inizio, sab_fine,
                dom_inizio, dom_fine
             ) VALUES (
                :idsede, :iddisciplina, :idutente, :nome_corso, :data_inizio_corso, :data_fine_corso, :quota_mensile_corso, :attivo,
                :lun_inizio, :lun_fine, :mar_inizio, :mar_fine, :mer_inizio, :mer_fine,
                :gio_inizio, :gio_fine, :ven_inizio, :ven_fine, :sab_inizio, :sab_fine,
                :dom_inizio, :dom_fine
             )'
        );
        $stmt->execute([
            'idsede' => $sedeId,
            'iddisciplina' => $disciplinaId,
            'idutente' => $userId,
            'nome_corso' => $name,
            'data_inizio_corso' => $startDate,
            'data_fine_corso' => $endDate,
            'quota_mensile_corso' => $monthlyFee,
            'attivo' => $active,
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

    public function readCorsoById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                c.idcorso AS id,
                c.idsede AS sede_id,
                c.iddisciplina AS disciplina_id,
                c.idutente AS user_id,
                COALESCE(c.nome_corso, '') AS name,
                COALESCE(s.sede, '') AS sede,
                COALESCE(d.disciplina, '') AS disciplina,
                TRIM(CONCAT(COALESCE(u.nome, ''), ' ', COALESCE(u.cognome, ''))) AS teacher,
                c.data_inizio_corso AS start_date,
                 c.data_fine_corso AS end_date,
                c.quota_mensile_corso AS monthly_fee,
                 COALESCE(c.attivo, 1) AS active,
                 COALESCE(c.immagine_corso, '') AS image_path,
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

    public function updateImmagineCorso(int $id, string $imagePath): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE corsi SET immagine_corso = :immagine_corso WHERE idcorso = :id');
        return $stmt->execute(['immagine_corso' => $imagePath, 'id' => $id]);
    }

    public function updateCorso(
        int $id,
        int $sedeId,
        int $disciplinaId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): bool {
        $name = trim($name);
        $active = $active === 0 ? 0 : 1;

        if ($id <= 0 || $sedeId <= 0 || $disciplinaId <= 0 || $userId <= 0 || $name === '') {
            throw new \InvalidArgumentException('Dati corso non validi per aggiornamento');
        }

        $this->assertActiveInstructor($userId);

        $orariNormalizzati = $this->normalizzaOrariSettimana($orari);

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'UPDATE corsi
             SET idsede = :idsede,
                 iddisciplina = :iddisciplina,
                 idutente = :idutente,
                 nome_corso = :nome_corso,
                 data_inizio_corso = :data_inizio_corso,
                 data_fine_corso = :data_fine_corso,
                 quota_mensile_corso = :quota_mensile_corso,
                 attivo = :attivo,
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
            'idsede' => $sedeId,
            'iddisciplina' => $disciplinaId,
            'idutente' => $userId,
            'nome_corso' => $name,
            'data_inizio_corso' => $startDate,
            'data_fine_corso' => $endDate,
            'quota_mensile_corso' => $monthlyFee,
            'attivo' => $active,
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

    private function assertActiveInstructor(int $userId): void
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT 1
             FROM utenti u
             INNER JOIN utenti_has_profili up ON up.idutente = u.idutente
             INNER JOIN profili p ON p.idprofilo = up.idprofilo
             WHERE u.idutente = :idutente
               AND u.cancellato = 0
               AND u.attivo = 1
               AND p.profilo = 'Istruttore'
             LIMIT 1"
        );
        $stmt->execute(['idutente' => $userId]);

        if ($stmt->fetchColumn() === false) {
            throw new \InvalidArgumentException('Seleziona un istruttore attivo');
        }
    }

    public function deleteCorso(int $id): bool
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID corso non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('DELETE FROM corsi WHERE idcorso = :id');
        return $stmt->execute(['id' => $id]);
    }
}
