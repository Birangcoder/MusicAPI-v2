<?php

declare(strict_types=1);
require_once __DIR__ . '/secrets.php';

date_default_timezone_set('Asia/Kolkata');

define('API_VERSION', 'v1');

define('API_NAME', 'MusicAPI');

define('APP_DEBUG', false); 
define('APP_NAME', 'MusicAPI');
define('APP_ENV', 'production');

define('APP_URL', 'http://localhost/MusicAPI-v2/');

define('JWT_SECRET', 'CHANGE_THIS_TO_RANDOM_64_CHAR_SECRET_KEY');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRE', 60 * 60 * 24 * 7);


define('DEFAULT_LIMIT', 20);
define('MAX_LIMIT', 100);

error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? '1' : '0');