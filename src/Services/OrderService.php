<?php

namespace Oyst\Services;

use Db;
use Order;
use OrderCarrier;
use Validate;

class OrderService {

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new OrderService();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function getTrackingNumber($id_order)
    {
        $id_order_carrier = Db::getInstance()->getValue('
            SELECT `id_order_carrier`
            FROM `'._DB_PREFIX_.'order_carrier`
            WHERE `id_order` = '.(int)$id_order);
        if ($id_order_carrier) {
            $order_carrier = new OrderCarrier($id_order_carrier);
            if (Validate::isLoadedObject($order_carrier)) {
                return $order_carrier->tracking_number;
            }
        } else {
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                return $order->shipping_number;
            }
        }
        return '';
    }
}
