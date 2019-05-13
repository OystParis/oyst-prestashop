<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Context;
use Currency;
use Customer;
use Db;
use Exception;
use Gender;
use Language;
use Order;
use OrderCarrier;
use OrderHistory;
use Oyst\Classes\Notification;
use Oyst\Classes\OrderBuilder;
use Shop;
use Validate;

class OrderService
{

    private $id_lang;
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new OrderService();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->id_lang = Language::getIdByIso('FR');
    }

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
        try {
            $order = new Order($id_order);
            if (!empty($order->id_lang)) {
                $this->id_lang = $order->id_lang;
            }
        } catch (Exception $e) {
            return array('error' => 'Failed to load order object');
        }
        if (Validate::isLoadedObject($order)) {

            $context = Context::getContext();

            $id_oyst = Notification::getOystIdByOrderId($id_order);
            $ip = CustomerService::getInstance()->getLastIpFromIdCustomer($order->id_customer);
            $customer = null;
            $gender_name = '';
            if (!empty($order->id_customer)) {
                $customer = new Customer($order->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    $gender = new Gender($customer->id_gender, $this->id_lang);
                    if (Validate::isLoadedObject($gender)) {
                        $gender_name = $gender->name;
                    }
                } else {
                    $customer = null;
                }
            }

            $order_state = $order->getCurrentOrderState();

            $order_details = $order->getProducts();

            //Complete cart products and get carriers list
//            foreach ($cart_products as &$cart_product) {
//                $cart_product['image'] = $context->link->getImageLink($cart_product['link_rewrite'], $cart_product['id_image']);
//
//                if (!empty($cart_product['id_product_attribute'])) {
//                    $attributes = Db::getInstance()->executeS("SELECT al.`id_attribute`, al.`name` value_name, agl.`public_name` attribute_name
//                            FROM "._DB_PREFIX_."product_attribute_combination pac
//                            INNER JOIN "._DB_PREFIX_."attribute a ON a.id_attribute = pac.id_attribute
//                            INNER JOIN "._DB_PREFIX_."attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang=".$this->id_lang.")
//                            INNER JOIN "._DB_PREFIX_."attribute_group_lang agl ON (a.id_attribute_group = agl.id_attribute_group AND agl.id_lang=".$this->id_lang.")
//                            WHERE pac.id_product_attribute=".$cart_product['id_product_attribute']);
//
//                    $cart_product['attributes'] = $attributes;
//                }
//            }

            $cart_rules = $order->getCartRules();

            //Addresses
            $address_delivery = null;
            if (!empty($order->id_address_delivery)) {
                $address = new Address($order->id_address_delivery);
                if (Validate::isLoadedObject($address)) {
                    $address_delivery = $address;
                }
            }

            //Applied carrier
            if (!empty($order->id_carrier)) {
                $selected_carrier_id = $order->id_carrier;
            } else {
                $selected_carrier_id = 0;
            }

            $selected_carrier_obj = new Carrier($selected_carrier_id, $this->id_lang);
            if (!Validate::isLoadedObject($selected_carrier_obj)) {
                $selected_carrier_obj = null;
            }

            $address_invoice = null;
            if (!empty($order->id_address_invoice)) {
                $address = new Address($order->id_address_invoice);
                if (Validate::isLoadedObject($address)) {
                    $address_invoice = $address;
                }
            }

            //Shop
            $shop = null;
            $response['shop'] = array();
            $shop_obj = new Shop($order->id_shop);
            if (Validate::isLoadedObject($shop_obj)) {
                $shop = $shop_obj;
            }

            //Currency
            $currency = new Currency($order->id_currency);
            if (!Validate::isLoadedObject($currency)) {
                $currency = null;
            }

            $checkoutBuilder = new OrderBuilder($this->id_lang);
            return $checkoutBuilder->buildOrder(
                $id_oyst,
                $ip,
                $order,
                $order_state,
                $customer,
                $gender_name,
                $order_details,
                $cart_rules,
                $context,
                $selected_carrier_obj,
                $address_delivery,
                $address_invoice,
                $shop,
                $currency
            );
        } else {
            $result['errors'] = "Order not exists";
        }

        return $result;
    }

    public function refund($id_order, $amount = 0)
    {
        if ($amount === 0) {
            try {
                $order = new Order($id_order);
                $amount = $order->getTotalPaid();
            } catch (\Exception $e) {}
        }

        $fields = [
            'orderAmounts' => [
                Notification::getOystIdByOrderId($id_order) => $amount
            ]
        ];
        $endpoint_result = \Oyst\Services\EndpointService::getInstance()->callEndpoint('refund', $fields);
    }

    public function cancelOrder($order_id)
    {
        $order = new Order($order_id);

        if (Validate::isLoadedObject($order)) {
            $cancel_os_id = OystStatusService::getInstance()->getPrestashopStatusIdFromOystStatus('oyst_canceled');
            $history = new OrderHistory();
            $history->id_order = $order->id;
            $history->id_employee = 0;
            $history->changeIdOrderState($cancel_os_id, $order);
            $history->add();

            //Then we remove the link between this order and oyst
            $notification = Notification::getNotificationByOrderId($order->id);
            if (!empty($notification)) {
                $notification->delete();
            }
        }
    }
}
