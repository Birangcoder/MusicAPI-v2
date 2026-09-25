<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

/*
|--------------------------------------------------------------------------
| Load environment variables
|--------------------------------------------------------------------------
|
| On Render, these values are provided by Render Environment Variables.
| Locally, secrets.php can provide the same values.
|
*/
$environment = getenv('APP_ENV') ?: 'development';

$secretsFile = __DIR__ . '/secrets.php';

if ($environment !== 'production' && file_exists($secretsFile)) {
    require_once $secretsFile;
}

/*
|--------------------------------------------------------------------------
| Application
|--------------------------------------------------------------------------
*/

define('API_VERSION', 'v2');

define('API_NAME', 'MusicAPI');

define('APP_DEBUG', false);

define('APP_NAME', 'MusicAPI');

define('APP_ENV', $environment);

define(
    'APP_URL',
    getenv('V2_APP_URL') ?: (getenv('APP_URL') ?: 'http://localhost/MusicAPI-v2/')
);

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

define(
    'DB_HOST',
    getenv('V2_DB_HOST') ?: 'localhost'
);

define(
    'DB_PORT',
    getenv('V2_DB_PORT') ?: '5432'
);

define(
    'DB_NAME',
    getenv('V2_DB_NAME') ?: 'postgres'
);

define(
    'DB_USER',
    getenv('V2_DB_USER') ?: 'postgres'
);

define(
    'DB_PASS',
    getenv('V2_DB_PASS') ?: ''
);

/*
|--------------------------------------------------------------------------
| JWT
|--------------------------------------------------------------------------
*/

define(
    'JWT_SECRET',
    getenv('JWT_SECRET') ?: 'change-this-in-local-secrets'
);

define(
    'JWT_ALGORITHM',
    'HS256'
);

define(
    'JWT_EXPIRE',
    60 * 60 * 24 * 7
);

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

define('DEFAULT_LIMIT', 20);

define('MAX_LIMIT', 100);

/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

ini_set(
    'display_errors',
    APP_ENV === 'development' ? '1' : '0'
);