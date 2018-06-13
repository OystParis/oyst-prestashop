<?php

namespace Oyst\Controller;

use Order;
use OrderSlip;
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
            $id_order = Notification::getOrderIdByOystOrderId($params['url']['id']);

            $response = OrderService::getInstance()->getOrder($id_order);
            if (empty($response['errors'])) {
                $this->respondAsJson($response);
            } else {
                $this->respondError(400, $response['errors']);
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function updateOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystOrderId($params['url']['id']);
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

                if (!empty($params['data']['refund'])) {
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
                        $result['refund'] = 'Order slip created successfully';
                    } else {
                        $result['refund'] = 'Order slip creation failed';
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
}
