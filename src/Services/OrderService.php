<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Customer;
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

    /**
     * @param $id_order
     * @return array
     */
    public function getOrder($id_order)
    {
        $result = array();
        //Get order info (carrier, state etc)
        $order = new Order($id_order);
        if (Validate::isLoadedObject($order)) {
            $result['order'] = json_decode(json_encode($order), true);
            $carrier = new Carrier($order->id_carrier);
            if (Validate::isLoadedObject($carrier)) {
                $result['order']['id_carrier_reference'] = $carrier->id_reference;
            }

            //Get order details
            $result['products'] = $order->getProductsDetail();

            //Get customer infos
            $result['customer'] = new Customer($order->id_customer);

            //Get addresses (invoice + delivery)
            $result['address_delivery'] = new Address($order->id_address_delivery);
            $result['address_invoice'] = new Address($order->id_address_invoice);
        } else {
            $result['errors'] = "Order not exists";
        }

        return $result;
    }
}
