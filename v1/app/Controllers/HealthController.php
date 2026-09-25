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

        $db = Database::getInstance()->connection();

        $connectionTime = microtime(true) - $start;

        $queryTimes = [];

        for ($i = 0; $i < 10; $i++) {
            $queryStart = microtime(true);

            $db->query("SELECT 1");

            $queryTimes[] = round(
                (microtime(true) - $queryStart) * 1000,
                2
            );
        }

        $totalTime = microtime(true) - $start;

        $this->success([
            'connection_time_ms' => round($connectionTime * 1000, 2),
            'query_times_ms' => $queryTimes,
            'total_time_ms' => round($totalTime * 1000, 2),
        ]);
    }
}