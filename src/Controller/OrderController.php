<?php

namespace Oyst\Controller;

use Cart;
use Configuration;
use Exception;
use Order;
use OrderSlip;
use Oyst;
use Oyst\Classes\Notification;
use Oyst\Services\OrderService;
use Validate;

class OrderController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('order');
    }

    public function getOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystId($params['url']['id']);
            $response = OrderService::getInstance()->getOrder($id_order);
            $this->respondAsJson($response);
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function updateOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystId($params['url']['id']);
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                $result = array();
                if (!empty($params['data']['id_order_state'])) {
                    if ($order->current_state != $params['data']['id_order_state']) {
                        $order->setCurrentState($params['data']['id_order_state']);
                        $result['change_order_state'] = array('success' => true);
                    } else {
                        $result['change_order_state'] = array('error' => 'The order already has this status');
                    }
                }

                $this->respondAsJson($result);
            } else {
                $this->respondError(400, 'Bad id_order');
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function createOrder($params)
    {
        if (!empty($params['data'])) {
            $notification = Notification::getNotificationByOystId($params['data']['oyst_id']);
            if (empty($notification)) {
                $this->respondError(400, 'Notification not found');
            } else {
                if ($notification->isAlreadyStarted()) {
                    $this->respondError(400, 'Order already on creation');
                } elseif ($notification->isAlreadyFinished()) {
                    $this->respondError(400, 'Order already created');
                } else {
                    $cart = new Cart($notification->cart_id);
                    if (Validate::isLoadedObject($cart)) {
                        $notification->start();
                        $oyst = new Oyst();
                        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
                        try {
                            if ($oyst->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $oyst->displayName, null, array(), (int)$cart->id_currency, false, $cart->secure_key)) {
                                $notification->complete($oyst->currentOrder);
                                $this->respondAsJson(OrderService::getInstance()->getOrder($oyst->currentOrder));
                            } else {
                                $this->respondError(400, 'Order creation failed');
                            }
                        } catch (Exception $e) {
                            $this->logger->error('Failed to transform cart '.$cart->id.' into order (Exception : '.$e->getMessage().')');
                            $this->respondError(500, 'Exception on order creation : '.$e->getMessage());
                        }
                    } else {
                        $this->respondError(400, 'Bad id_cart');
                    }
                }
            }
        } else {
            $this->respondError(400, 'data is missing');
        }
    }

    public function refundOrder($params)
    {
        if (!empty($params['url']['id'])) {
            if (!empty($params['data']['refund'])) {
                $id_order = Notification::getOrderIdByOystId($params['url']['id']);
                $order = new Order($id_order);

                if (!empty($params['data']['refund']['total'])) {
                    $amount = 0;
                    $amount_choosen = false;
                    $products_list = array();
                    //Get order details
                    foreach ($order->getProductsDetail() as $order_detail) {
                        $products_list[] = array(
                            'id_order_detail' => $order_detail['id_order_detail'],
                            'unit_price' => $order_detail['unit_price_tax_excl'],
                            'quantity' => $order_detail['product_quantity'],
                        );
                    };
                    $shipping_cost = $order->total_shipping_tax_excl;

                    if (OrderSlip::create($order, $products_list, $shipping_cost, $amount, $amount_choosen)) {
                        $this->respondAsJson('Order slip created successfully');
                    } else {
                        $this->respondError(400, 'Order slip creation failed');
                    }
                } elseif($params['data']['refund']['partial']) {
                    //TODO Manage partial refund
                }
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function createOrderFromCart($params)
    {
        if (!empty($params['data']['id_cart'])) {

        } else {
            $this->respondError(400, 'id_cart is missing');
        }
    }
}
