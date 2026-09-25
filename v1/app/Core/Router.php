<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->add('POST', $uri, $action);
    }

    public function put(string $uri, callable|array $action): void
    {
        $this->add('PUT', $uri, $action);
    }

    public function delete(string $uri, callable|array $action): void
    {
        $this->add('DELETE', $uri, $action);
    }

    public function patch(string $uri, callable|array $action): void
    {
        $this->add('PATCH', $uri, $action);
    }

    public function options(string $uri, callable|array $action): void
    {
        $this->add('OPTIONS', $uri, $action);
    }

    private function add(string $method, string $uri, callable|array $action): void
    {
        $uri = trim($uri, '/');
        $this->routes[$method][$uri] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = trim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $route => $action) {

            $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                if (is_array($action)) {
                    [$controller, $function] = $action;
                    $instance = new $controller();
                    call_user_func_array([$instance, $function], $matches);
                    return;
                }

                call_user_func_array($action, $matches);
                return;
            }
        }

        http_response_code(404);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
    }
}