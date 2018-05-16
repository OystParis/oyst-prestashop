<?php

namespace Oyst\Controller;

use Cart;
use Exception;
use Oyst\Classes\FileLogger;
use Validate;

class CartController extends AbstractOystController
{
    public function _construct()
    {
        $this->logger = new FileLogger();
        $this->logger->setFile(dirname(__FILE__).'/../../logs/cart.log');
    }

    public function getCart($params)
    {
        if (!empty($params['url']['id'])) {
            $cart = new Cart((int)$params['url']['id']);
            if (Validate::isLoadedObject($cart)) {
                $response = array();
                try {
                    $response['products'] = $cart->getProducts(true);
                    $response['cart_rules'] = $cart->getCartRules();
                    $response['total'] = $cart->getOrderTotal();
                    $response['carriers'] = $cart->getDeliveryOptionList();
                    $this->respondAsJson($response);
                } catch(Exception $e) {
                    print_r($e);
                }
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
        }
    }

    public function updateCart($params)
    {
        echo "updatCart<pre>";
        print_r($params);
        echo "</pre>";
        exit;
    }
}
