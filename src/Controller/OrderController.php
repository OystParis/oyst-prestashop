<?php

namespace Oyst\Controller;

use Cart;
use Exception;
use Oyst;
use Validate;
use Configuration;

class OrderController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('order');
    }

    public function createOrderFromCart($params)
    {
        if (!empty($params['data']['id_cart'])) {
            $cart = new Cart((int)$params['data']['id_cart']);
            if (Validate::isLoadedObject($cart)) {
                //TODO save on db => start

                $oyst = new Oyst();
                $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

                try {
                    if ($oyst->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $oyst->displayName, NULL, array(), (int)$cart->id_currency, false, $cart->secure_key)) {
                        $this->respondAsJson('Order created with id : '.$oyst->currentOrder);
                        //TODO Save on db => end + id_order
                    } else {
                        $this->respondError(400, 'Order creation failed');
                    }
                } catch(Exception $e) {
                    $this->logger->error('Failed to transform cart '.$cart->id.' into order (Exception : '.$e->getMessage().')');
                    $this->respondError(500, 'Exception on order creation : '.$e->getMessage());
                }
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
        } else {
            $this->respondError(400, 'id_cart is missing');
        }
    }
}
