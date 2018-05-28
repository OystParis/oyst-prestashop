<?php

namespace Oyst\Controller;

use Address;
use Carrier;
use Cart;
use Currency;
use Customer;
use Customization;
use Exception;
use Message;
use Product;
use Shop;
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

                    $this->respondAsJson($response);
                } catch(Exception $e) {
                    print_r($e);
                }
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
        }
    }

    public function updateCart($params)
    {
        echo "updatCart<pre>";
        print_r($params);
        echo "</pre>";
        exit;
    }
}
