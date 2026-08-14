<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

abstract class Controller
{
    protected function body(): array
    {
        return Request::body();
    }

    protected function query(
        string $key,
        mixed $default = null
    ): mixed {
        return Request::query($key, $default);
    }

    protected function page(): int
    {
        return Request::page();
    }

    protected function limit(): int
    {
        return Request::limit();
    }

    protected function offset(): int
    {
        return Request::offset();
    }

    protected function user(): array
    {
        return AuthMiddleware::user();
    }

    protected function userId(): int
    {
        return AuthMiddleware::userId();
    }

    protected function success(
        mixed $data = null,
        string $message = 'Success'
    ): never {
        Response::json($data, $message);
    }

    protected function created(
        mixed $data = null,
        string $message = 'Created'
    ): never {
        Response::created($data, $message);
    }

    protected function error(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): never {
        Response::error(
            $message,
            $status,
            $errors
        );
    }

    protected function notFound(
        string $message = 'Not Found'
    ): never {
        Response::notFound($message);
    }

    protected function unauthorized(
        string $message = 'Unauthorized'
    ): never {
        Response::unauthorized($message);
    }
}