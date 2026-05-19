<?php

declare(strict_types=1);

namespace App\Services;

final class AtletiService extends BaseService
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
}
