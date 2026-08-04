<?php

declare(strict_types=1);

namespace App\Services;

final class SediService extends BaseService
{
    public function readSedi(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idsede AS id,
                COALESCE(sede, '') AS name,
                COALESCE(codice_sede, '') AS code,
                COALESCE(attiva, 1) AS active
             FROM sedi
             ORDER BY idsede DESC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function addSede(string $name, string $code, int $active = 1): array
    {
        $name = trim($name);
        $code = strtoupper(trim($code));
        $active = $active === 0 ? 0 : 1;

        if ($name === '') {
            throw new \InvalidArgumentException('Il nome sede non puo essere vuoto');
        }

        if ($code === '') {
            $code = preg_replace('/\s+/', '_', strtoupper($name)) ?: 'SEDE';
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('INSERT INTO sedi (sede, codice_sede, attiva) VALUES (:sede, :codice_sede, :attiva)');
        $stmt->execute([
            'sede' => $name,
            'codice_sede' => $code,
            'attiva' => $active,
        ]);

        return [
            'id' => (int) $pdo->lastInsertId(),
            'name' => $name,
            'code' => $code,
            'active' => $active,
        ];
    }

    public function findSedeById(int $id): array
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID sede non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                idsede AS id,
                COALESCE(sede, '') AS name,
                COALESCE(codice_sede, '') AS code,
                COALESCE(attiva, 1) AS active
             FROM sedi
             WHERE idsede = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    public function updateSede(int $id, string $name, string $code, int $active = 1): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID sede non valido');
        }

        $name = trim($name);
        $code = strtoupper(trim($code));
        $active = $active === 0 ? 0 : 1;

        if ($name === '') {
            throw new \InvalidArgumentException('Il nome sede non puo essere vuoto');
        }

        if ($code === '') {
            $code = preg_replace('/\s+/', '_', strtoupper($name)) ?: 'SEDE';
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE sedi SET sede = :sede, codice_sede = :codice_sede, attiva = :attiva WHERE idsede = :id');
        $stmt->execute([
            'id' => $id,
            'sede' => $name,
            'codice_sede' => $code,
            'attiva' => $active,
        ]);
    }

    public function deleteSede(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID sede non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('DELETE FROM sedi WHERE idsede = :id');
        $stmt->execute(['id' => $id]);
    }

    public function readSediPage(int $start, int $length, string $search = '', string $orderColumn = 'id', string $orderDir = 'desc', bool $activeOnly = false): array
    {
        $orderColumn = in_array($orderColumn, ['id', 'name', 'code', 'active'], true) ? $orderColumn : 'id';
        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';
        $search = trim($search);

        $pdo = db_connection();

        $countStmt = $pdo->query('SELECT COUNT(*) AS total FROM sedi');
        $countRow = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $total = (int) ($countRow['total'] ?? 0);

        $conditions = [];
        $params = [];

        if ($activeOnly) {
            $conditions[] = 'attiva = :activeOnly';
            $params['activeOnly'] = 1;
        }

        if ($search !== '') {
            $conditions[] = '(sede LIKE :search OR codice_sede LIKE :search OR CAST(attiva AS CHAR) LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereClause = '';
        if (count($conditions) > 0) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $filteredStmt = $pdo->prepare(
            "SELECT COUNT(*) AS count FROM sedi " . $whereClause
        );
        $filteredStmt->execute($params);
        $filteredRow = $filteredStmt->fetch(\PDO::FETCH_ASSOC);
        $filtered = (int) ($filteredRow['count'] ?? 0);

        $columnMap = ['id' => 'idsede', 'name' => 'sede', 'code' => 'codice_sede', 'active' => 'attiva'];
        $orderColumnDb = $columnMap[$orderColumn] ?? 'idsede';

        $dataStmt = $pdo->prepare(
            "SELECT
                idsede AS id,
                COALESCE(sede, '') AS name,
                COALESCE(codice_sede, '') AS code,
                COALESCE(attiva, 1) AS active
             FROM sedi
             " . $whereClause . "
             ORDER BY " . $orderColumnDb . " " . $orderDir . "
             LIMIT :start, :length"
        );

        foreach ($params as $key => $value) {
            $dataStmt->bindValue(':' . $key, $value);
        }
        $dataStmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $dataStmt->bindValue(':length', $length, \PDO::PARAM_INT);
        $dataStmt->execute();

        $rows = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => is_array($rows) ? $rows : [],
        ];
    }
}
