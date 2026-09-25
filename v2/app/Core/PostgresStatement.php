<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class PostgresStatement
{
    private \PDOStatement $statement;
    private array $bound = [];
    private ?PostgresResult $result = null;
    private int $affectedRows = 0;

    public function __construct(PDO $pdo, string $sql)
    {
        $this->statement = $pdo->prepare($sql);
    }

    public function bind_param(string $types, &...$vars): void
    {
        $this->bound = [];
        foreach ($vars as $i => &$value) {
            $type = $types[$i] ?? 's';
            $pdoType = match ($type) {
                'i' => PDO::PARAM_INT,
                'b' => PDO::PARAM_LOB,
                default => PDO::PARAM_STR,
            };
            $this->bound[] = [$i + 1, &$value, $pdoType];
        }
    }

    public function execute(): bool
    {
        foreach ($this->bound as [$position, &$value, $pdoType]) {
            $this->statement->bindValue($position, $value, $pdoType);
        }

        $ok = $this->statement->execute();
        $this->affectedRows = $this->statement->rowCount();
        $this->affected_rows = $this->affectedRows;
        $this->result = null;

        return $ok;
    }

    public function get_result(): PostgresResult
    {
        if ($this->result === null) {
            $this->result = new PostgresResult($this->statement);
            $this->num_rows = $this->result->num_rows;
        }

        return $this->result;
    }

    public function store_result(): void
    {
        if ($this->result === null) {
            $this->result = new PostgresResult($this->statement);
            $this->num_rows = $this->result->num_rows;
        }
    }

    public function bind_result(&...$vars): void
    {
        if ($this->result === null) {
            $this->result = new PostgresResult($this->statement);
        }
        $this->result->bind_result($vars);
    }

    public function fetch(): bool
    {
        if ($this->result === null) {
            $this->result = new PostgresResult($this->statement);
        }
        return $this->result->fetchBound();
    }

    public function close(): void
    {
        $this->result = null;
    }

    public int $num_rows = 0;
    public int $affected_rows = 0;
    public int $insert_id = 0;
}

