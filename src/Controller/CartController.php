<?php

namespace Oyst\Controller;

use Address;
use Carrier;
use Cart;
use Configuration;
use Currency;
use Customer;
use Exception;
use Message;
use Order;
use Oyst;
use Oyst\Classes\Notification;
use Product;
use Tools;
use Validate;
use Warehouse;

class CartController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('cart');
    }

    public function getCart($params)
    {
        if (!empty($params['url']['id'])) {
            $cart = new Cart((int)$params['url']['id']);
            if (Validate::isLoadedObject($cart)) {
                $errors = array();
                $response = array();
                try {
                    $response['cart'] = $cart;
                    $response['products'] = $cart->getProducts(true);
                    $carriers = array();
                    foreach ($response['products'] as &$product) {
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
                            $product_carriers = Carrier::getAvailableCarrierList(new Product($product['id_product']), $warehouse['id_warehouse'], $cart->id_address_delivery, $cart->id_shop, $cart, $errors);
                            if (empty($carriers)) {
                                $carriers = $product_carriers;
                            } else {
                                $carriers = array_intersect($carriers, $product_carriers);
                            }
                        }
                    }
                    if (!empty($errors)) {
                        $this->respondError(400, 'Error on carriers recuperation : '.print_r($errors, true));
                    }
                    $response['carriers'] = $carriers;

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
                    $order_id = Order::getIdByCartId($cart->id);
                    if (!empty($order_id)) {
                        $response['order'] = array(
                            'order_id' => $order_id,
                            'order_reference' => Order::getUniqReferenceOf($order_id),
                            'oyst_order_id' => Notification::getOystOrderIdByOrderId($order_id)
                        );
                    }
                    $this->respondAsJson($response);
                } catch(Exception $e) {
                    print_r($e);
                }
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
        } else {
            $this->respondError(400, 'id_cart is missing');
        }
    }

    public function updateCart($params)
    {
        if (!empty($params['url']['id'])) {
            //TODO Manage PUT arguments for update carrier, address etc

            if (!empty($params['data']['finalize'])) {
                $this->createOrderFromCart($params);
            }
            $this->getCart($params);
        } else {
            $this->respondError(400, 'id_cart is missing');
        }
    }

    public function createOrderFromCart($params)
    {
        if (empty($params['data']['id_oyst_order'])) {
            $this->respondError(400, 'id_oyst_order is missing');
        }

        $cart = new Cart((int)$params['url']['id']);
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
                    return $oyst->currentOrder;
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
