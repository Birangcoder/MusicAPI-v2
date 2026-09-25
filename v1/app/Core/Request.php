<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public static function body(): array
    {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        return is_array($data) ? $data : [];
    }

    public static function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $_GET[$key] ?? $default;
    }

    public static function input(
        string $key,
        mixed $default = null
    ): mixed {

        $body = self::body();

        return $body[$key] ?? $default;
    }

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function uri(): string
    {
        return parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );
    }

    public static function bearerToken(): ?string
    {
        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        $authorization =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? '';

        if (
            preg_match(
                '/Bearer\s+(.*)$/i',
                $authorization,
                $matches
            )
        ) {
            return trim($matches[1]);
        }

        return null;
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function page(): int
    {
        return max(
            1,
            (int)($_GET['page'] ?? 1)
        );
    }

    public static function limit(): int
    {
        $limit = (int)($_GET['limit'] ?? DEFAULT_LIMIT);

        if ($limit < 1) {
            $limit = DEFAULT_LIMIT;
        }

        if ($limit > MAX_LIMIT) {
            $limit = MAX_LIMIT;
        }

        return $limit;
    }

    public static function offset(): int
    {
        return (self::page() - 1) * self::limit();
    }
}