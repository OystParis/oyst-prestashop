<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Cart;
use Context;
use Currency;
use Customer;
use Db;
use Exception;
use Gender;
use Language;
use Oyst\Classes\CheckoutBuilder;
use Oyst\Classes\Notification;
use Product;
use Shop;
use Tools;
use Validate;
use Warehouse;

class CartService {

    private $id_lang;
    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CartService();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->id_lang = Language::getIdByIso('FR');
    }

    private function __clone() {}

    public function getCart($id_cart)
    {
        $response = array();
        $cart = new Cart((int)$id_cart);
        if (!empty($cart->id_lang)) {
            $this->id_lang = $cart->id_lang;
        }

        if (Validate::isLoadedObject($cart)) {
            try {
                $context = Context::getContext();

                $id_oyst = Notification::getOystIdByCartId($cart->id);
                $ip = CustomerService::getInstance()->getLastIpFromIdCustomer($cart->id_customer);
                $customer = null;
                $gender_name = '';
                if (!empty($cart->id_customer)) {
                    $customer = new Customer($cart->id_customer);
                    if (Validate::isLoadedObject($customer)) {
                        $gender = new Gender($customer->id_gender, $this->id_lang);
                        if (Validate::isLoadedObject($gender)) {
                            $gender_name = $gender->name;
                        }
                    } else {
                        $customer = null;
                    }
                }

                $cart_products = $cart->getProductsWithSeparatedGifts();

                //Complete cart products and get carriers list
                $carriers = array();
                foreach ($cart_products as &$cart_product) {
                    $cart_product['image'] = $context->link->getImageLink($cart_product['link_rewrite'], $cart_product['id_image']);

                    if (!empty($cart_product['id_product_attribute'])) {
                        $attributes = Db::getInstance()->executeS("SELECT al.`id_attribute`, al.`name` value_name, agl.`public_name` attribute_name 
                            FROM "._DB_PREFIX_."product_attribute_combination pac
                            INNER JOIN "._DB_PREFIX_."attribute a ON a.id_attribute = pac.id_attribute
                            INNER JOIN "._DB_PREFIX_."attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang=".$this->id_lang.")
                            INNER JOIN "._DB_PREFIX_."attribute_group_lang agl ON (a.id_attribute_group = agl.id_attribute_group AND agl.id_lang=".$this->id_lang.")
                            WHERE pac.id_product_attribute=".$cart_product['id_product_attribute']);

                        $cart_product['attributes'] = $attributes;
                    }

                    $warehouse_list = Warehouse::getProductWarehouseList($cart_product['id_product'], $cart_product['id_product_attribute'], $cart->id_shop);
                    if (count($warehouse_list) == 0) {
                        $warehouse_list = Warehouse::getProductWarehouseList($cart_product['id_product'], $cart_product['id_product_attribute']);
                    }
                    if (empty($warehouse_list)) {
                        $warehouse_list = array(0 => array('id_warehouse' => 0));
                    }

                    //Get availables carriers for cart
                    foreach ($warehouse_list as $warehouse) {
                        $product_carriers = Carrier::getAvailableCarrierList(new Product($cart_product['id_product']), $warehouse['id_warehouse'], $cart->id_address_delivery, $cart->id_shop, $cart);
                        if (empty($carriers)) {
                            $carriers = $product_carriers;
                        } else {
                            $carriers = array_intersect($carriers, $product_carriers);
                        }
                    }

                    //Get customizations
                    $customizations = $cart->getProductCustomization($cart_product['id_product']);
                    if (!empty($customizations)) {
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
                        $cart_product['customizations'] = $customizations;
                    }
                }

                $cart_rules = $cart->getCartRules();

                //Addresses
                $address_delivery = null;
                if (!empty($cart->id_address_delivery)) {
                    $address = new Address($cart->id_address_delivery);
                    if (Validate::isLoadedObject($address)) {
                        $address_delivery = $address;
                    }
                }

                //Available carriers
                $available_carriers = array();
                foreach ($carriers as $id_carrier) {
                    $carrier_obj = new Carrier($id_carrier, $this->id_lang);
                    if (Validate::isLoadedObject($carrier_obj)) {
                        $available_carriers[] = $carrier_obj;
                    }
                }

                //Applied carrier
                if (!empty($cart->id_carrier)) {
                    $selected_carrier_id = $cart->id_carrier;
                } else {
                    //Get default shipping from store preferences
                    $delivery_option = $cart->getDeliveryOption();
                    if (!empty($delivery_option[$cart->id_address_delivery])) {
                        $tmp = explode(',', $delivery_option[$cart->id_address_delivery]);
                        $selected_carrier_id = $tmp[0];
                    } else {
                        //Get first carrier if no one match with preferences
                        if (!empty($carriers[0])) {
                            $selected_carrier_id = $carriers[0];
                        }
                    }
                }

                $selected_carrier_obj = new Carrier($selected_carrier_id, $this->id_lang);
                if (!Validate::isLoadedObject($selected_carrier_obj)) {
                    $selected_carrier_obj = null;
                }

                $address_invoice = null;
                if (!empty($cart->id_address_invoice)) {
                    $address = new Address($cart->id_address_invoice);
                    if (Validate::isLoadedObject($address)) {
                        $address_invoice = $address;
                    }
                }

                //Shop
                $shop = null;
                $response['shop'] = array();
                $shop_obj = new Shop($cart->id_shop);
                if (Validate::isLoadedObject($shop_obj)) {
                    $shop = $shop_obj;
                }

                //Currency
                $currency = new Currency($cart->id_currency);
                if (!Validate::isLoadedObject($currency)) {
                    $currency = null;
                }

                $checkoutBuilder = new CheckoutBuilder($this->id_lang);
                return $checkoutBuilder->buildCheckout(
                    $id_oyst,
                    $ip,
                    $cart,
                    $customer,
                    $gender_name,
                    $cart_products,
                    $cart_rules,
                    $context,
                    $available_carriers,
                    $selected_carrier_obj,
                    $address_delivery,
                    $address_invoice,
                    $shop,
                    $currency
                );
            } catch(Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        } else {
            $response['errors'][] = 'Bad id_cart';
        }
        return $response;
    }


}
