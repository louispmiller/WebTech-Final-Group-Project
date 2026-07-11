<?php
// Author: Sidi Mohamed Ebnou Oumar

namespace App\Core;

class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $pattern = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', rtrim($path, '/')) . '$#';
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                ($route['handler'])(...$matches);
                return;
            }
        }

        Response::error('Not found', 404);
    }
}
