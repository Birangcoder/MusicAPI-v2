<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Helpers\Response;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(function (Throwable $e) {

            Response::serverError(
                APP_ENV === 'development'
                    ? $e->getMessage()
                    : 'Internal Server Error'
            );

        });
    }
}