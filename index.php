<?php

declare(strict_types=1);

// One Render service exposes both APIs:
//   /v1/* -> MySQL API
//   /v2/* -> PostgreSQL/Supabase API

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');

if ($path === '/v1' || str_starts_with($path, '/v1/')) {
    $version = 'v1';
} elseif ($path === '/v2' || str_starts_with($path, '/v2/')) {
    $version = 'v2';
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid API version. Use /v1 or /v2.'
    ]);
    exit;
}

// Remove /v1 or /v2 before handing the request to the selected API.
$subPath = substr($path, strlen('/' . $version));
if ($subPath === '') {
    $subPath = '/';
}

$query = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
$_SERVER['REQUEST_URI'] = $subPath . ($query ? '?' . $query : '');

// Child API files use relative paths, so execute from their directory.
$apiRoot = __DIR__ . DIRECTORY_SEPARATOR . $version;
chdir($apiRoot);
require $apiRoot . DIRECTORY_SEPARATOR . 'index.php';
