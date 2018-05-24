<?php

namespace Oyst\Controller;

use Cart;
use Exception;
use Oyst;
use Oyst\Classes\Notification;
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
        if (empty($params['data']['id_cart'])) {
            $this->respondError(400, 'id_cart is missing');
        }

        if (empty($params['data']['id_oyst_order'])) {
            $this->respondError(400, 'id_oyst_order is missing');
        }

        $cart = new Cart((int)$params['data']['id_cart']);
        if (Validate::isLoadedObject($cart)) {
            $notification = Notification::getNotificationByOystOrderId($params['data']['id_oyst_order']);

            if ($notification->isAlreadyFinished()) {
                $this->respondError(400, 'Order already created');
            }

            if ($notification->isAlreadyStarted()) {
                $this->respondError(400, 'Order already on creation');
            }

            $notification->start();
            $oyst = new Oyst();
            $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

            try {
                if ($oyst->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $oyst->displayName, NULL, array(), (int)$cart->id_currency, false, $cart->secure_key)) {
                    $notification->complete($oyst->currentOrder);
                    $this->logger->info('Cart '.$cart->id.' transformed into order '.$oyst->currentOrder);
                    $this->respondAsJson('Order created with id : '.$oyst->currentOrder);
                } else {
                    $this->respondError(400, 'Order creation failed');
                }
            } catch(Exception $e) {
                $this->logger->error('Failed to transform cart '.$cart->id.' into order (Exception : '.$e->getMessage().')');
                $this->respondError(400, 'Exception on order creation : '.$e->getMessage());
            }
        } else {
            $this->respondError(400, 'Bad id_cart');
        }
    }
}
