<?php

declare(strict_types=1);

namespace App\Core;

class Api
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): never {

        http_response_code($code);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function error(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null
    ): never {

        http_response_code($code);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function notFound(
        string $message = 'Not Found'
    ): never {

        self::error($message, 404);
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