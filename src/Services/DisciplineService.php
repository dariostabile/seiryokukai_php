<?php

declare(strict_types=1);

namespace App\Services;

final class DisciplineService extends BaseService
{
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

    public function findDisciplineById(int $id): array
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID disciplina non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            "SELECT
                iddisciplina AS id,
                COALESCE(disciplina, '') AS name,
                COALESCE(note_disciplina, '') AS notes
             FROM discipline
             WHERE iddisciplina = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    public function updateDiscipline(int $id, string $name, string $notes = ''): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID disciplina non valido');
        }

        $name = trim($name);
        $notes = trim($notes);

        if ($name === '') {
            throw new \InvalidArgumentException('Il nome disciplina non puo essere vuoto');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('UPDATE discipline SET disciplina = :disciplina, note_disciplina = :note_disciplina WHERE iddisciplina = :id');
        $stmt->execute([
            'id' => $id,
            'disciplina' => $name,
            'note_disciplina' => $notes !== '' ? $notes : null,
        ]);
    }

    public function deleteDiscipline(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID disciplina non valido');
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare('DELETE FROM discipline WHERE iddisciplina = :id');
        $stmt->execute(['id' => $id]);
    }

    public function readDisciplinesPage(int $start, int $length, string $search = '', string $orderColumn = 'id', string $orderDir = 'desc'): array
    {
        $orderColumn = in_array($orderColumn, ['id', 'name', 'notes'], true) ? $orderColumn : 'id';
        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';
        $search = trim($search);

        $pdo = db_connection();

        $countStmt = $pdo->query('SELECT COUNT(*) AS total FROM discipline');
        $countRow = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $total = (int) ($countRow['total'] ?? 0);

        $whereClause = '';
        $params = [];

        if ($search !== '') {
            $whereClause = 'WHERE disciplina LIKE :search OR note_disciplina LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $filteredStmt = $pdo->prepare(
            "SELECT COUNT(*) AS count FROM discipline " . $whereClause
        );
        $filteredStmt->execute($params);
        $filteredRow = $filteredStmt->fetch(\PDO::FETCH_ASSOC);
        $filtered = (int) ($filteredRow['count'] ?? 0);

        $columnMap = ['id' => 'iddisciplina', 'name' => 'disciplina', 'notes' => 'note_disciplina'];
        $orderColumnDb = $columnMap[$orderColumn] ?? 'iddisciplina';

        $dataStmt = $pdo->prepare(
            "SELECT
                iddisciplina AS id,
                COALESCE(disciplina, '') AS name,
                COALESCE(note_disciplina, '') AS notes
             FROM discipline
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
