<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected PostgresConnection $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connection();
    }

    protected function fetchAll(PostgresStatement $stmt): array
    {
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    protected function fetchOne(PostgresStatement $stmt): ?array
    {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    protected function execute(PostgresStatement $stmt): bool
    {
        $status = $stmt->execute();
        $stmt->close();
        return $status;
    }

    protected function insertId(): int
    {
        return (int)$this->db->lastInsertId();
    }
}
