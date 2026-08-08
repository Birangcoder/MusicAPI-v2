<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Middleware/CorsMiddleware.php';

\App\Middleware\CorsMiddleware::handle();

spl_autoload_register(function ($class) {

    $prefix = 'App\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $class = substr($class, strlen($prefix));

    $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;

$router = new Router();

require ROOT_PATH . '/routes/api.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = '/MusicAPI-v2/public';

if (strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}

$uri = trim($uri, '/');

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $uri
);