<?php
/**
 * Simple Front Controller Router
 */

class Router
{
    private array $routes = [];
    private string $method;
    private string $uri;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Remove base path
        if (BASE_PATH && str_starts_with($uri, BASE_PATH)) {
            $uri = substr($uri, strlen(BASE_PATH)) ?: '/';
        }
        $this->uri = rtrim($uri, '/') ?: '/';
    }

    public function get(string $path, callable|array $handler): self
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
        return $this;
    }

    public function post(string $path, callable|array $handler): self
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
        return $this;
    }

    private function normalize(string $path): string
    {
        return rtrim($path, '/') ?: '/';
    }

    public function dispatch(): void
    {
        $path = $this->uri;
        $method = $this->method;

        // Exact match
        if (isset($this->routes[$method][$path])) {
            $this->call($this->routes[$method][$path]);
            return;
        }

        // Pattern match with {id}
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $this->call($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'ไม่พบหน้า']);
    }

    private function call(callable|array $handler, array $params = []): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
        } else {
            call_user_func_array($handler, $params);
        }
    }
}
