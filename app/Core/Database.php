<?php

declare(strict_types=1);

namespace App\Core;

use mysqli;
use mysqli_sql_exception;

class Database
{
    private static ?Database $instance = null;

    private mysqli $connection;

    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->connection = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            (int) DB_PORT
        );

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function connection(): mysqli
    {
        return $this->connection;
    }
}