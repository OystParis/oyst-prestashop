<?php

namespace Oyst\Controller;

use Order;
use Oyst\Classes\Notification;
use Validate;

class OrderController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('order');
    }

    public function updateOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystOrderId($params['url']['id']);
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                if (!empty($params['data']['id_order_state'])) {
                    if ($order->current_state != $params['data']['id_order_state']) {
                        $order->setCurrentState($params['data']['id_order_state']);
                        $this->respondAsJson(array('success' => true));
                    } else {
                        $this->respondAsJson(array('error' => 'The order already has this status'));
                    }
                }
            } else {
                $this->respondError(400, 'Bad id_order');
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }
}
