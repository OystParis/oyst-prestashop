<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require dirname(__FILE__).'/../../vendor/autoload.php';
require _PS_ROOT_DIR_.'/init.php';

use Oyst\Classes\Route;
use Oyst\Classes\CurrentRequest;
use Oyst\Classes\OystAPIKey;

class OystDispatcherModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        //All routes are prefixed by /oyst-oneclick
        Route::addRoute('GET', '/v1/config/ecommerce', 'Config', 'getEcommerce');
        Route::addRoute('PUT', '/v1/config/oyst', 'Config', 'setOyst');
        Route::addRoute('GET', '/v1/checkout/{id}', 'Checkout', 'getCart');
        Route::addRoute('PUT', '/v1/checkout/{id}', 'Checkout', 'updateCart');
        Route::addRoute('PUT', '/v1/order/{id}/status', 'Order', 'changeStatus');
        Route::addRoute('POST', '/v1/order', 'Order', 'createOrder');
        Route::addRoute('POST', '/v1/order/{id}/refund', 'Order', 'refundOrder');
        Route::addRoute('GET', '/v1/lifecycle/tasks', 'Lifecycle', 'getTasks');
        Route::addRoute('GET', '/v1/lifecycle/informations', 'Lifecycle', 'getInformations');

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
            if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $_SERVER['PHP_AUTH_USER'] = $matches[1];
            }

            //set http auth headers for apache+php-cgi work around if variable gets renamed by apache
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Bearer\s+(.*)$/i', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
                $_SERVER['PHP_AUTH_USER'] = $matches[1];
            }

            if (isset($_GET['ws_key'])) {
                $key = $_GET['ws_key'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $key = $_SERVER['PHP_AUTH_USER'];
            } else {
                header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                header('WWW-Authenticate: Basic realm="Welcome to Oyst Webservice, please enter the authentication key as the login. No password required."');
                $this->printError(401, 'Bad API key');
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

        //Check if function need arguments
        $reflection = new ReflectionMethod($controller, $method);
        if ($reflection->getNumberOfParameters() == 1) {
            if (!empty($params)) {
                $controller_obj->$method($params);
            } else {
                $this->printError(400, 'Parameters are missing or invalid');
            }
        } else {
            $controller_obj->$method();
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
        die(json_encode(array('error' => $msg)));
    }
}
