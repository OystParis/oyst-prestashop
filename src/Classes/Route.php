<?php

namespace Oyst\Classes;

class Route
{
    private static $routes = array();

    public static function addRoute($http_method, $uri, $controller, $method, $required_auth = true)
    {
        self::$routes[$http_method][] = array(
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method,
            'required_auth' => $required_auth,
        );
    }

    public static function getRoute($http_method, $uri)
    {
        foreach (self::$routes[$http_method] as $route) {
            $params_name = array();
            preg_match_all('/{(.*)}/U', $route['uri'], $params_name);
            $regex = str_replace('/', '\/', preg_replace('/{.*}/U', '([^/]*)', $route['uri']));

            $params_value = array();
            if (preg_match('/^'.$regex.'$/', $uri, $params_value)) {
                $params = array();
                if (!empty($params_name[1])) {
                    foreach ($params_name[1] as $key => $param_name) {
                        $params[$param_name] = $params_value[$key+1];
                    }
                }
                $route['url_params'] = $params;
                return $route;
            }
        }
        return null;
    }
}
