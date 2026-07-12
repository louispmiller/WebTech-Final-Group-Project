<?php
// Author: Ojong Bessong NKONGHO
// Central router for the API. All routes are registered in index.php
// and this class handles matching and dispatching them.
// I chose a simple array-based approach instead of a framework router
// because it's easier to understand and debug for a team project like this.

class Router
{
    private $routes = [];

    // register a route - called from index.php for each endpoint
    // $handler is always [ControllerClassName, 'methodName']
    public function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // strip the base path (/smart-city) from the URI so route definitions
        // can just say /api/cities instead of /smart-city/api/cities
        $config   = require __DIR__ . '/../config/config.php';
        $basePath = $config['app']['base_path'] ?? '';

        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                $this->invoke($route['handler']);
                return;
            }
        }

        // no route matched
        Response::error('Endpoint not found', 404);
    }

    private function invoke($handler)
    {
        // support for plain callables if needed later
        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        // standard case: [ClassName, method]
        // instantiate the controller with a fresh db connection
        list($class, $method) = $handler;
        $db         = Database::getConnection();
        $controller = new $class($db);
        $controller->$method();
    }
}