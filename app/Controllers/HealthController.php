<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HealthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /db-test
    |--------------------------------------------------------------------------
    */

    public function dbTest(): void
    {
        $start = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Database Connection
        |--------------------------------------------------------------------------
        */

        $connectionStart = microtime(true);

        $db = Database::getInstance()->connection();

        $connectionTime = microtime(true) - $connectionStart;

        /*
        |--------------------------------------------------------------------------
        | Simple Query
        |--------------------------------------------------------------------------
        */

        $queryStart = microtime(true);

        $result = $db->query("SELECT 1");

        $queryTime = microtime(true) - $queryStart;

        /*
        |--------------------------------------------------------------------------
        | Total Time
        |--------------------------------------------------------------------------
        */

        $totalTime = microtime(true) - $start;

        $this->success([
            'database' => [
                'connection_time_ms' => round($connectionTime * 1000, 2),
                'query_time_ms' => round($queryTime * 1000, 2),
                'total_time_ms' => round($totalTime * 1000, 2),
            ]
        ]);
    }
}