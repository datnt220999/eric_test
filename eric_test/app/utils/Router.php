<?php
// app/utils/Router.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class Router
{
    // Lưu trữ các routes
    protected static $routes = [];

    // Định nghĩa route GET
    public static function get($uri, $controllerAction, $middleware = [])
    {
        self::$routes['GET'][$uri] = [
            'action' => $controllerAction,
            'middleware' => $middleware,
        ];
    }

    // Định nghĩa route POST
    public static function post($uri, $controllerAction, $middleware = [])
    {
        self::$routes['POST'][$uri] = [
            'action' => $controllerAction,
            'middleware' => $middleware,
        ];
    }

    // Định nghĩa route PUT
    public static function put($uri, $controllerAction, $middleware = [])
    {
        self::$routes['PUT'][$uri] = [
            'action' => $controllerAction,
            'middleware' => $middleware,
        ];
    }

    // Dispatch route
    public static function dispatch($uri, $method, $routes = [])
    {
        //Nếu route được truyền vào từ tham số thì sẽ sử dụng
        if (!empty($routes)) {
            self::loadRoutes($routes);
        }
        $uri = strtok($uri, '?');

        if (isset(self::$routes[$method][$uri])) {
            $route = self::$routes[$method][$uri];
            $action = $route['action'];
            $middleware = $route['middleware'];
            // Run middleware before the controller action.
            if(!empty($middleware)){
                if(!self::runMiddleware($middleware)){
                    return;
                }
            }
            self::executeAction($action);
        } else {
            Response::send(404, "Route {$uri} not found for method {$method}");
        }
    }

    private static function runMiddleware($middleware)
    {
        foreach ($middleware as $middlewareName) {
            if ($middlewareName == 'auth') {
                if (!AuthMiddleware::checkToken()) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function loadRoutes($routes)
    {
        foreach ($routes as $route) {
            self::$routes[$route['method']][$route['uri']] = [
                'action' => $route['controller'],
                'middleware' => $route['middleware'] ?? [],
            ];
        }
    }

    private static function executeAction($action)
    {
        list($controller, $method) = explode('@', $action);
        // Kiểm tra nếu file controller tồn tại
        $controllerFile = __DIR__ . "/../controllers/{$controller}.php";
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $method)) {
                $controllerInstance->$method();
            } else {
                Response::send(404, "Method {$method} not found in {$controller}");
            }
        } else {
            Response::send(404, "Controller {$controller} not found");
        }
    }
}