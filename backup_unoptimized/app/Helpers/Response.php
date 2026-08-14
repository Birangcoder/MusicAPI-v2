<?php

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    public static function json(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): never {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function error(
        string $message = 'Error',
        int $status = 400,
        mixed $errors = null
    ): never {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function created(
        mixed $data = null,
        string $message = 'Created Successfully'
    ): never {
        self::json($data, $message, 201);
    }

    public static function unauthorized(
        string $message = 'Unauthorized'
    ): never {
        self::error($message, 401);
    }

    public static function forbidden(
        string $message = 'Forbidden'
    ): never {
        self::error($message, 403);
    }

    public static function notFound(
        string $message = 'Resource Not Found'
    ): never {
        self::error($message, 404);
    }

    public static function validation(
        mixed $errors
    ): never {
        self::error(
            'Validation Failed',
            422,
            $errors
        );
    }

    public static function serverError(
        string $message = 'Internal Server Error'
    ): never {
        self::error($message, 500);
    }
}