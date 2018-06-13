<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Cart;
use Context;
use Currency;
use Customer;
use Exception;
use Message;
use Order;
use Oyst\Classes\Notification;
use Product;
use Tools;
use Validate;
use Warehouse;

class CartService {

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CartService();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function getCart($id_cart)
    {
        $response = array();
        $cart = new Cart((int)$id_cart);
        if (Validate::isLoadedObject($cart)) {
            try {
                $context = Context::getContext();
                $response['cart'] = $cart;
                $response['products'] = $cart->getProducts(true);
                $carriers = array();
                $carriers_errors = array();
                foreach ($response['products'] as &$product) {
                    //Get image link
                    $product['image'] = $context->link->getImageLink($product['link_rewrite'], $product['id_image']);

                    //Get customizations
                    if (!empty($product['id_customization'])) {
                        $customizations = $cart->getProductCustomization($product['id_product']);
                        foreach ($customizations as &$customization) {
                            if ($customization['type'] == Product::CUSTOMIZE_FILE) {
                                $customization['type_name'] = 'file';
                                $customization['value'] = Tools::getShopDomainSsl(true).'/upload/'.$customization['value'];
                            } elseif ($customization['type'] == Product::CUSTOMIZE_TEXTFIELD) {
                                $customization['type_name'] = 'textfield';
                            } else {
                                $customization['type_name'] = 'undefined';
                            }
                        }
                        $product['customizations'] = $customizations;
                    }

                    $warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute'], $cart->id_shop);
                    if (count($warehouse_list) == 0) {
                        $warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute']);
                    }
                    if (empty($warehouse_list)) {
                        $warehouse_list = array(0 => array('id_warehouse' => 0));
                    }

                    //Get availables carriers for cart
                    foreach ($warehouse_list as $warehouse) {
                        $product_carriers = Carrier::getAvailableCarrierList(new Product($product['id_product']), $warehouse['id_warehouse'], $cart->id_address_delivery, $cart->id_shop, $cart, $carriers_errors);
                        if (empty($carriers)) {
                            $carriers = $product_carriers;
                        } else {
                            $carriers = array_intersect($carriers, $product_carriers);
                        }
                    }
                }
                if (!empty($carriers_errors)) {
                    $response['errors'][] = 'Error on carriers recuperation : '.print_r($carriers_errors, true);
                } else {
                    $response['available_carriers'] = $carriers;
                }

                //Message
                $message = Message::getMessageByCartId($cart->id);
                $response['message'] = $message['message'];

                //Customer
                if (!empty($cart->id_customer)) {
                    $customer = new Customer($cart->id_customer);
                    if (Validate::isLoadedObject($customer)) {
                        $response['customer'] = $customer;
                    }
                }

                //Address
                if (!empty($cart->id_address_delivery)) {
                    $address = new Address($cart->id_address_delivery);
                    if (Validate::isLoadedObject($address)) {
                        $response['address'] = $address;
                    }
                }

                $response['cart_rules'] = $cart->getCartRules();
                $response['total'] = $cart->getOrderTotal();
                $currency = new Currency($cart->id_currency);
                $response['currency'] = $currency->iso_code;

                //Check if cart is linked to an order
                $response['order'] = array();
                $order = Order::getByCartId($cart->id);
                if (Validate::isLoadedObject($order)) {
                    $response['order'] = array(
                        'order_id' => $order->id,
                        'order_reference' => $order->reference,
                        'oyst_order_id' => Notification::getOystOrderIdByOrderId($order->id),
                        'id_order_state' => $order->current_state,
                        'tracking' => OrderService::getInstance()->getTrackingNumber($order->id)
                    );
                }
            } catch(Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        } else {
            $response['errors'][] = 'Bad id_cart';
        }
        return $response;
    }

}
