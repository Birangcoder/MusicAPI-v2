<?php

namespace App\Controllers;

class HealthController
{
    public function dbTest()
    {
        header('Content-Type: application/json');

        $start = microtime(true);

        // Database connection
        $connectionStart = microtime(true);

        $conn = new \mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );

        $connectionTime = microtime(true) - $connectionStart;

        if ($conn->connect_error) {
            http_response_code(500);

            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed',
                'connection_time_ms' => round(
                    $connectionTime * 1000,
                    2
                ),
            ]);

            return;
        }

        // Simple query
        $queryStart = microtime(true);

        $result = $conn->query('SELECT 1');

        $queryTime = microtime(true) - $queryStart;

        if (!$result) {
            http_response_code(500);

            echo json_encode([
                'status' => 'error',
                'message' => 'Database query failed',
                'query_time_ms' => round(
                    $queryTime * 1000,
                    2
                ),
            ]);

            $conn->close();
            return;
        }

        $totalTime = microtime(true) - $start;

        echo json_encode([
            'status' => 'ok',
            'database' => [
                'connection_time_ms' => round(
                    $connectionTime * 1000,
                    2
                ),
                'query_time_ms' => round(
                    $queryTime * 1000,
                    2
                ),
            ],
            'total_time_ms' => round(
                $totalTime * 1000,
                2
            ),
        ]);

        $conn->close();
    }
}