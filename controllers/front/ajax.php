<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystAjaxModuleFrontController extends ModuleFrontController
{
    public function displayAjax()
    {
        header('Content-Type: application/json');

        if (empty($_GET['action'])) {
            die(json_encode(array('error' => 'Action undefined')));
        }

        switch ($_GET['action']) {
            case 'get_id_cart':
                $context = Context::getContext();
                $id_cart = $context->cart->id;
                //Add id_cart to cookie to retrieve cart on order confirmation
                $context->cookie->oyst_id_cart = $context->cart->id;
                die(json_encode(array('id_cart' => $id_cart)));
                break;

            case 'check_http_authorization':
                if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                    die(json_encode(array('http_authorization' => 1)));
                } else {
                    die(json_encode(array('http_authorization' => 0)));
                }
                break;

            default:
                die(json_encode(array('error' => 'Unknown action')));
        }
    }
}
