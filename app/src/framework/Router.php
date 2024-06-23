<?php

namespace App;

use App\Middleware\userRole;

class Router {
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static array $globalMiddlewares = [];
    private static array $groupMiddlewares = [];
    private static string $prefix = '';
    private static $currentRoute = null;

    private static function addRoute(string $method, string $path, $callback, string $name = null): Router
    {
        $fullPath = self::$prefix . $path;
        $route = [
            'method' => $method,
            'path' => $fullPath,
            'callback' => $callback,
            'where' => [],
            'middlewares' => array_merge(self::$groupMiddlewares, self::$globalMiddlewares)
        ];

        if ($name) {
            self::$namedRoutes[$name] = $route;
        }

        self::$routes[] = $route;
        self::$currentRoute = count(self::$routes) - 1;

        return new self();
    }

    public static function get($path, $callback, string $name = null): Router
    {
        return self::addRoute('GET', $path, $callback, $name);
    }

    public static function post($path, $callback, string $name = null): Router
    {
        return self::addRoute('POST', $path, $callback, $name);
    }

    public static function put($path, $callback, string $name = null): Router
    {
        return self::addRoute('PUT', $path, $callback, $name);
    }

    public static function patch($path, $callback, string $name = null): Router
    {
        return self::addRoute('PATCH', $path, $callback, $name);
    }

    public static function delete($path, $callback, string $name = null): Router
    {
        return self::addRoute('DELETE', $path, $callback, $name);
    }

    public static function options($path, $callback, string $name = null): Router
    {
        return self::addRoute('OPTIONS', $path, $callback, $name);
    }

    public static function addMatch($methods, $path, $callback, string $name = null): Router
    {
        foreach ($methods as $method) {
            self::addRoute($method, $path, $callback, $name);
        }
        return new self();
    }

    public static function any($path, $callback, string $name = null): Router
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        return self::addMatch($methods, $path, $callback, $name);
    }

    public static function generateUrl(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route {$name} not defined.");
        }

        $route = self::$namedRoutes[$name];
        $path = $route['path'];

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return url($path);
    }

    public static function middleware($middleware): Router
    {
        if (!is_callable($middleware) && !is_string($middleware) && !is_array($middleware)) {
            throw new \Exception("Invalid middleware type");
        }

        if (self::$currentRoute !== null) {
            self::$routes[self::$currentRoute]['middlewares'][] = $middleware;
        } else {
            self::$globalMiddlewares[] = $middleware;
        }
        return new self();
    }

    public static function group($attributes, $callback): void
    {
        $parentPrefix = self::$prefix;
        $parentGroupMiddlewares = self::$groupMiddlewares;

        if (isset($attributes['prefix'])) {
            self::$prefix .= $attributes['prefix'];
        }
        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            self::$groupMiddlewares = array_merge(self::$groupMiddlewares, $middlewares);
        }

        call_user_func($callback);

        self::$prefix = $parentPrefix;
        self::$groupMiddlewares = $parentGroupMiddlewares;
    }

    public static function where($param, $regex): Router
    {
        if (self::$currentRoute !== null) {
            self::$routes[self::$currentRoute]['where'][$param] = $regex;
        }
        return new self();
    }

    public static function view($path, $viewName, $data = []): Router
    {
        return self::addRoute('GET', $path, function($params = []) use ($viewName, $data) {
            View::render($viewName, array_merge($data, $params));
        });
    }

    public static function api($path, $apiName, $data = []): Router
    {
        return self::addRoute('POST', $path, function($params = []) use ($apiName, $data) {
            extract(array_merge($data, $params));
            require ROOT . "\\resources\api\\$apiName.php";
        });
    }

    private static function getUri() {
        return $_GET['route'] ?? '/';
    }

    public static function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = self::getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::match($path, $route['path'], $params)) {
                if (self::validateParams($params, $route['where'])) {
                    foreach ($route['middlewares'] as $middleware) {
                        if (is_callable($middleware)) {
                            call_user_func($middleware);
                        } elseif (is_array($middleware) && class_exists($middleware[0])) {
                            (new $middleware[0])->handle(...array_slice($middleware, 1));
                        } elseif (class_exists($middleware)) {
                            (new $middleware)->handle();
                        } else {
                            throw new \Exception("Invalid middleware configuration");
                        }
                    }
                    echo call_user_func_array($route['callback'], [$params]);
                    return;
                }
            }
        }

        if ($method !== 'GET') {
            showError(405, 405);
        } else {
            showError(404, 404);
        }
    }

    private static function match($requestPath, $routePath, &$params) {
        $requestParts = explode('/', trim($requestPath, '/'));
        $routeParts = explode('/', trim($routePath, '/'));

        if (count($requestParts) !== count($routeParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $key => $part) {
            if (preg_match('/^{\w+}$/', $part)) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $requestParts[$key];
            } elseif ($requestParts[$key] !== $part) {
                return false;
            }
        }

        return true;
    }

    private static function validateParams($params, $conditions) {
        foreach ($conditions as $key => $regex) {
            if (!isset($params[$key]) || !preg_match($regex, $params[$key])) {
                return false;
            }
        }
        return true;
    }
}
