<?php

namespace wfm;

class Router
{
    // когда роутер получает маршрут от браузера, мы ищем его в таблице маршрутов
    protected static array $routes = []; // таблица маршрутов
    protected static array $route = []; // нужный маршрут запихиваем сюда

    // добавление нужного роута в таблица маршрутов
    public static function add($regexp, $route = [])
    {
        self::$routes[$regexp] = $route;
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function getRoute(): array
    {
        return self::$route;
    }

    protected static function removeQueryString($url)
    {
        if ($url) {
            $params = explode('&', $url, 2);
            if (false === str_contains($params[0], '=')) {
                return rtrim($params[0], '/');
            }
        }
        return '';
    }

    public static function dispatch($url)
    {
//        debug( self::$routes, 1);
//        var_dump('Before, url:' . $url . '<br>');
        $url = self::removeQueryString($url);
//        var_dump('After, url:' . $url . '<br>');

        if (self::matchRoute($url)) {
//            debug(self::$route);
            if (!empty(self::$route['lang'])) {
                App::$app->setProperty('lang', self::$route['lang']);
            }
            // все контроллеры будут находиться в app/controllers
            $controller = 'app\controllers\\' . self::$route['admin_prefix'] . self::$route['controller'] . 'Controller';

            if (class_exists($controller)) {
                // создаем контроллер и передаем в него текущий путь
                $controllerObject = new $controller(self::$route);

                $controllerObject->getModel();

                $action = self::lowerCamelCase(self::$route['action'] . 'Action');
                if (method_exists($controllerObject, $action)) {
                    // у контроллера должны вызвать action - метод контроллера
                    $controllerObject->$action();
                    $controllerObject->getView();
                } else {
                    throw new \Exception("Метод {$controller}::{$action} не найден", 404);
                }
            } else {
                throw new \Exception("Контроллер {$controller} не найден", 404);
            }

        } else {
            throw new \Exception("Страница не найдена", 404);
        }
    }

    public static function matchRoute($url): bool
    {
        foreach (self::$routes as $pattern => $route) {
            // ищем соответствие с $url. Запомненое попадает в $matches
            if (preg_match("#{$pattern}#", $url, $matches)) {
                foreach ($matches as $k => $v) {
                    if (is_string($k)) {
                        $route[$k] = $v;
                    }
                }
                // Action может не быть, тогда по умолчанию назначаем index.
                if (empty($route['action'])) {
                    $route['action'] = 'index';
                }
                // admin prefix может быть а может и нет
                if (!isset($route['admin_prefix'])) {
                    $route['admin_prefix'] = '';
                } else {
                    $route['admin_prefix'] .= '\\';
                }
//                debug($route);
                $route['controller'] = self::upperCamelCase($route['controller']);
//                debug($route);
                self::$route = $route;
                return true;

            }
        }
        return false;
    }

    // CamelCase (new-product => NewProduct)
    protected static function upperCamelCase($name): string
    {
        //  str_replace - new-product => new product;
        // ucwords - new product - New Product
        // str_replace('-', ' ', $name - удаляем пробел
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

    // camelCase
    protected static function lowerCamelCase($name): string
    {
        return lcfirst(self::upperCamelCase($name));
    }


}