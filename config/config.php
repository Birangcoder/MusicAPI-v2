<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

define('API_VERSION', 'v2');

define('API_NAME', 'MusicAPI');

define('APP_DEBUG', true);
define('APP_NAME', 'MusicAPI V2');
define('APP_ENV', 'development');

define('APP_URL', 'http://localhost/MusicAPI-v2/public');

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'music_app_v2');
define('DB_USER', 'root');
define('DB_PASS', '');

define('JWT_SECRET', 'CHANGE_THIS_TO_RANDOM_64_CHAR_SECRET_KEY');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRE', 60 * 60 * 24 * 7);

define('UPLOAD_SONG_PATH', dirname(__DIR__) . '/uploads/songs/');
define('UPLOAD_COVER_PATH', dirname(__DIR__) . '/uploads/covers/');
define('UPLOAD_ARTIST_PATH', dirname(__DIR__) . '/uploads/artists/');

define('DEFAULT_LIMIT', 20);
define('MAX_LIMIT', 100);

error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'development' ? '1' : '0');