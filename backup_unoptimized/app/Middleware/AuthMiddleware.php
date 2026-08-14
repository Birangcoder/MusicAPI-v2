<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\JWT;
use App\Helpers\Response;

class AuthMiddleware
{
    public static function handle(): array
    {
        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $authorization =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? '';

        if (
            empty($authorization) ||
            !preg_match('/Bearer\s(\S+)/', $authorization, $matches)
        ) {
            Response::unauthorized('Authorization token required.');
        }

        $payload = JWT::decode($matches[1]);

        if ($payload === false) {
            Response::unauthorized('Invalid or expired token.');
        }

        return $payload;
    }

    public static function userId(): int
    {
        $payload = self::handle();

        return (int)($payload['user_id'] ?? 0);
    }

    public static function user(): array
    {
        return self::handle();
    }

    public static function optionalUserId(): int
    {
        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $authorization =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? '';

        if (
            empty($authorization) ||
            !preg_match('/Bearer\s(\S+)/', $authorization, $matches)
        ) {
            return 0;
        }

        $payload = JWT::decode($matches[1]);

        if ($payload === false) {
            return 0;
        }

        return (int)($payload['user_id'] ?? 0);
    }
}