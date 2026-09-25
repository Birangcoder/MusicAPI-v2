<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class PostgresConnection
{
    private PDO $pdo;

    public function __construct()
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function prepare(string $sql): PostgresStatement
    {
        return new PostgresStatement($this->pdo, $sql);
    }

    public function query(string $sql): PostgresResult
    {
        return new PostgresResult($this->pdo->query($sql));
    }

    public function lastInsertId(): int
    {
        return (int)$this->pdo->query('SELECT LASTVAL()')->fetchColumn();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}

