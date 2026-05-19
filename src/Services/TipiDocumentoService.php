<?php

declare(strict_types=1);

namespace App\Services;

final class TipiDocumentoService extends BaseService
{
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

    public function findDocumentTypeById(int $id): array
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID tipo documento non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                idtipo_documento AS id,
                COALESCE(tipo_documento, '') AS type
             FROM tipi_documento
             WHERE idtipo_documento = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    public function updateDocumentType(int $id, string $type): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID tipo documento non valido');
        }

        $type = trim($type);

        if ($type === '') {
            throw new \InvalidArgumentException('Il tipo documento non puo essere vuoto');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE tipi_documento SET tipo_documento = :tipo_documento WHERE idtipo_documento = :id');
        $stmt->execute([
            'id' => $id,
            'tipo_documento' => $type,
        ]);
    }

    public function deleteDocumentType(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID tipo documento non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('DELETE FROM tipi_documento WHERE idtipo_documento = :id');
        $stmt->execute(['id' => $id]);
    }

    public function readDocumentTypesPage(int $start, int $length, string $search = '', string $orderColumn = 'id', string $orderDir = 'desc'): array
    {
        $orderColumn = in_array($orderColumn, ['id', 'type'], true) ? $orderColumn : 'id';
        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';
        $search = trim($search);

        $pdo = db_connection();

        $countStmt = $pdo->query('SELECT COUNT(*) AS total FROM tipi_documento');
        $countRow = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $total = (int) ($countRow['total'] ?? 0);

        $whereClause = '';
        $params = [];

        if ($search !== '') {
            $whereClause = 'WHERE tipo_documento LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $filteredStmt = $pdo->prepare(
            "SELECT COUNT(*) AS count FROM tipi_documento " . $whereClause
        );
        $filteredStmt->execute($params);
        $filteredRow = $filteredStmt->fetch(\PDO::FETCH_ASSOC);
        $filtered = (int) ($filteredRow['count'] ?? 0);

        $columnMap = ['id' => 'idtipo_documento', 'type' => 'tipo_documento'];
        $orderColumnDb = $columnMap[$orderColumn] ?? 'idtipo_documento';

        $dataStmt = $pdo->prepare(
            "SELECT
                idtipo_documento AS id,
                COALESCE(tipo_documento, '') AS type
             FROM tipi_documento
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
