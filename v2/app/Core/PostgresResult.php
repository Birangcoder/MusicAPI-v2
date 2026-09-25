<?php

declare(strict_types=1);

namespace App\Core;

class PostgresResult
{
    public int $num_rows = 0;
    private array $rows = [];
    private int $index = 0;
    private array $boundVars = [];

    public function __construct($source)
    {
        if ($source instanceof \PDOStatement) {
            $this->rows = $source->fetchAll(\PDO::FETCH_ASSOC);
            $this->num_rows = count($this->rows);
        }
    }

    public function fetch_assoc(): ?array
    {
        if (!isset($this->rows[$this->index])) {
            return null;
        }

        return $this->rows[$this->index++];
    }

    public function bind_result(array &$vars): void
    {
        $this->boundVars = &$vars;
    }

    public function fetchBound(): bool
    {
        $row = $this->fetch_assoc();
        if ($row === null) {
            return false;
        }

        $values = array_values($row);
        foreach ($this->boundVars as $i => &$var) {
            $var = $values[$i] ?? null;
        }

        return true;
    }

}