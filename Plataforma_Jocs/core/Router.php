<?php
class Router
{
    private $routes = [];
    private $views = [];

    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function delete($path, $controller, $method)
    {
        $this->routes['DELETE'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function view($path, $file)
    {
        $this->views[$path] = $file;
    }

    private function volJson()
    {
        if (defined('API_MODE') || in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'], true)) {
            return true;
        }
        return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_GET['route'] ?? '/';

        if (!isset($this->routes[$method][$path])) {
            if ($this->volJson()) {
                json_error('Ruta no trobada', 404);
            }
            die('Ruta no trobada: ' . htmlspecialchars($path));
        }

        if (!$this->volJson()) {
            if (!isset($this->views[$path])) {
                http_response_code(404);
                die('Vista no trobada');
            }
            include __DIR__ . '/../' . $this->views[$path];
            exit;
        }

        $route = $this->routes[$method][$path];
        $controller = new $route['controller']();
        $controller->{$route['method']}();
    }
}
