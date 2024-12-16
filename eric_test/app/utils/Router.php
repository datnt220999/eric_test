<?php
// app/Router.php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class Router
{
    // Lưu trữ các routes
    protected static $routes = [];

    // Định nghĩa route GET
    public static function get($uri, $controllerAction,$middlaware=[])
    {
        self::$routes['GET'][$uri]['action'] = $controllerAction;
        self::$routes['GET'][$uri]['middlaware'] = $middlaware;
    }

    // Định nghĩa route POST
    public static function post($uri, $controllerAction,$middlaware=[])
    {
        self::$routes['POST'][$uri]['action'] = $controllerAction;
        self::$routes['POST'][$uri]['middlaware'] = $middlaware;
    }
    public static function put($uri, $controllerAction,$middlaware=[])
    {
        self::$routes['PUT'][$uri]['action'] = $controllerAction;
        self::$routes['PUT'][$uri]['middlaware'] = $middlaware;
    }

    // Chạy route tương ứng với URI và method
    public static function dispatch($uri, $method)
    {
        $uri = strtok($uri, '?');
        if (isset(self::$routes[$method][$uri])) {
            $action = self::$routes[$method][$uri]['action'];
            $middlawares = self::$routes[$method][$uri]['middlaware'];
            foreach ($middlawares as $middlaware ){
                if($middlaware == 'auth'){
                    $auth =AuthMiddleware::checkToken();
                }

            }
            list($controller, $method) = explode('@', $action);

            // Kiểm tra nếu file controller tồn tại
            $controllerFile = __DIR__ . "/../controllers/{$controller}.php";
//            dd($controllerFile);
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
        } else {
            Response::send(404, "Route {$uri} not found for method {$method}");
        }
    }
}
