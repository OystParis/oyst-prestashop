<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../../../init.php';

use Oyst\Classes\Route;
use Oyst\Classes\CurrentRequest;
use Oyst\Classes\OystAPIKey;

class OystDispatcherModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        //All routes are prefixed by /oyst
        Route::addRoute('GET', '/cart/{id}', 'Cart', 'getCart');
        Route::addRoute('GET', '/config', 'Config', 'getConfig');
        Route::addRoute('PUT', '/script-tag', 'ScriptTag', 'setUrl');
        Route::addRoute('PUT', '/customer/search', 'Customer', 'search');
        Route::addRoute('PUT', '/cart/{id}', 'Cart', 'updateCart');
        Route::addRoute('PUT', '/order/create', 'Order', 'createOrderFromCart');

        $request = new CurrentRequest();

        if (empty($_GET['request'])) {
            $this->printError(400, 'No route specified');
        }

        $request_route = '/'.$_GET['request'];

        $route = Route::getRoute($request->getMethod(), $request_route);

        if (empty($route)) {
            $this->printError(400, 'Route not found');
        }

        //Check auth
        if ($route['required_auth']) {
            //set http auth headers for apache+php-cgi work around
            if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                list($name, $password) = explode(':', base64_decode($matches[1]));
                $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
            }

            //set http auth headers for apache+php-cgi work around if variable gets renamed by apache
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
                list($name, $password) = explode(':', base64_decode($matches[1]));
                $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
            }

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $key = $_SERVER['PHP_AUTH_USER'];
            } elseif (isset($_GET['ws_key'])) {
                $key = $_GET['ws_key'];
            } else {
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                header('WWW-Authenticate: Basic realm="Welcome to Oyst Webservice, please enter the authentication key as the login. No password required."');
                die('401 Unauthorized');
            }

            if (!OystAPIKey::isKeyActive($key)) {
                $this->printError(401, 'Bad API key');
            }
        }

        $params = array();

        if (!empty($route['url_params'])) {
            $params['url'] = $route['url_params'];
        }

        $data = $request->getJson();

        if (!empty($data)) {
            $params['data'] = $data;
        }
        $controller = 'Oyst\\Controller\\'.$route['controller'].'Controller';

        $method = $route['method'];
        $controller_obj = new $controller($request);

        $controller_obj->logger->info(
            sprintf(
                "New call from route %s (%s@%s) [%s]",
                $request->getMethod().' '.$request->getRequestUri(),
                $route['controller'],
                $route['method'],
                (!empty($params) ? print_r($params, true) : '')
            )
        );
        if (empty($params)) {
            $controller_obj->$method();
        } else {
            $controller_obj->$method($params);
        }
        exit;
    }

    public function printError($code, $msg = '')
    {
        if (!function_exists('http_response_code')) {
            $text = '';
            switch ($code) {
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
            }
            header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$text);
        } else {
            http_response_code($code);
        }
        die($msg);
    }
}
