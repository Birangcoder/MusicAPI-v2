<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected \mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connection();
    }

    protected function fetchAll(\mysqli_stmt $stmt): array
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

    protected function fetchOne(\mysqli_stmt $stmt): ?array
    {
        $stmt->execute();

        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $stmt->close();

        return $row ?: null;
    }

    protected function execute(\mysqli_stmt $stmt): bool
    {
        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    protected function insertId(): int
    {
        return $this->db->insert_id;
    }
}