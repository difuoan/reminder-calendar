<?php
/**
 * HTTP Router
 *
 * A minimal front-controller router that matches the incoming
 * request method + URI path against registered routes and calls
 * the corresponding controller action.
 *
 * Route parameters (e.g. /api/appointments/42) are captured via
 * named regex groups and passed as the second argument to the
 * handler callable.
 *
 * Usage:
 *   $router = new Router();
 *   $router->get('/', [HomeController::class, 'index']);
 *   $router->post('/login', [AuthController::class, 'login']);
 *   $router->dispatch();
 */

declare(strict_types=1);

namespace App;

class Router
{
    /**
     * Registered routes indexed by HTTP method.
     * Each entry: ['pattern' => string, 'handler' => callable]
     *
     * @var array<string, list<array{pattern: string, handler: callable}>>
     */
    private array $routes = [];

    // ── Route registration ────────────────────────────────────────────────────

    /** Register a GET route. */
    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /** Register a POST route. */
    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /** Register a PUT route. */
    public function put(string $path, callable|array $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    /** Register a DELETE route. */
    public function delete(string $path, callable|array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    /**
     * Internal helper – converts a URI pattern with :param placeholders
     * into a named-capture-group regex and stores it.
     *
     * @param string          $method  HTTP verb (uppercase)
     * @param string          $path    URI pattern, e.g. /api/appointments/:id
     * @param callable|array  $handler Controller callback [ClassName::class, 'method']
     */
    private function add(string $method, string $path, callable|array $handler): void
    {
        // Convert :id → (?P<id>[^/]+) for named capture groups
        $pattern = preg_replace('/:([a-z_]+)/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    /**
     * Match the current request against registered routes and invoke
     * the handler, or send a 404/405 response if no match is found.
     */
    public function dispatch(): void
    {
        // Support method override via POST field _method (for PUT/DELETE from forms)
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Strip query string from the URI
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        // Remove the script's base path if the app is in a subdirectory
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Collect only named captures (skip integer-indexed entries)
                $params = array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                );

                // Instantiate controller class if handler is [ClassName, method]
                [$class, $action] = $route['handler'];
                (new $class())->$action($params);
                return;
            }
        }

        // No route matched
        if (!empty($this->routes[$method])) {
            http_response_code(404);
            require __DIR__ . '/Views/404.php';
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
    }
}
