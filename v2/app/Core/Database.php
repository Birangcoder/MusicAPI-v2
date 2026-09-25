<?php

declare(strict_types=1);

namespace App\Core;

class Database
{
    private static ?Database $instance = null;
    private PostgresConnection $connection;

    private function __construct()
    {
        $this->connection = new PostgresConnection();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function connection(): PostgresConnection
    {
        return $this->connection;
    }
}
