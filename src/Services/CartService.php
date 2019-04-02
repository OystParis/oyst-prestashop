<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Cart;
use CartRule;
use Context;
use Currency;
use Customer;
use Db;
use Exception;
use Gender;
use Language;
use Oyst\Classes\CheckoutBuilder;
use Oyst\Classes\Notification;
use Oyst\Services\VersionCompliance\Helper;
use Oyst\Services\VersionCompliance\Helper as ServicesHelper;
use Product;
use Shop;
use Tools;
use Validate;
use Warehouse;

class CartService
{
    private $id_lang;
    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CartService();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->id_lang = Language::getIdByIso('FR');
    }

    private function __clone()
    {
        //
    }

    public function getCart($id_cart)
    {
        $response = array();
        $cart = new Cart((int)$id_cart);
        if (!empty($cart->id_lang)) {
            $this->id_lang = $cart->id_lang;
        }
        $context = Context::getContext();

        if (Validate::isLoadedObject($cart)) {
            //Fill context because prestashop can't retrieve it from a server call
            $context->cart = $cart;

            try {
                $helper = new Helper();

                $id_oyst = Notification::getOystIdByCartId($cart->id);
                $customer = null;
                $gender_name = '';
                $ip = '';
                if (!empty($cart->id_customer)) {
                    $ip = CustomerService::getInstance()->getLastIpFromIdCustomer($cart->id_customer);
                    $customer = new Customer($cart->id_customer);
                    $context->customer = $customer;
                    if (Validate::isLoadedObject($customer)) {
                        $gender = new Gender($customer->id_gender, $this->id_lang);
                        if (Validate::isLoadedObject($gender)) {
                            $gender_name = $gender->name;
                        }
                    } else {
                        $customer = null;
                    }
                }

                $cart_products = $helper->getCartProductsWithSeparatedGifts($cart);

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
                    $selected_carrier_id = $this->getCartDefaultShipmentId($cart);
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
            } catch (Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        } else {
            $response['errors'][] = 'Bad id_cart';
        }
        return $response;
    }

    public function updateCart($cart, $data)
    {
        $errors = [];

        $context = Context::getContext();
        $context->cart = $cart;
        $context->currency = new Currency($cart->id_currency);
        $context->shop = new Shop($cart->id_shop);

        //Products
        $helper = new ServicesHelper();
        $cart_products = $helper->getCartProductsWithSeparatedGifts($cart);

        if (!empty($data['items'])) {
            $oyst_product_list = [];
            foreach ($data['items'] as $product) {
                $oyst_product_list[] = $product['internal_reference'];
                $ids = explode('-', $product['internal_reference']);
                $id_product = (isset($ids[0]) ? $ids[0] : 0);
                $id_product_attribute = (isset($ids[1]) ? $ids[1] : 0);

                //TODO Manage customization
                if ($product['quantity'] <= 0) {
                    $cart->deleteProduct($id_product, $id_product_attribute);
                } else {
                    $cart_product_quantity = 0;
                    foreach ($cart_products as $cart_product) {
                        if ($cart_product['id_product'] == $id_product && $cart_product['id_product_attribute'] == $id_product_attribute) {
                            $cart_product_quantity = $cart_product['cart_quantity'];
                        }
                    }
                    if ($product['quantity'] < $cart_product_quantity) {
                        $cart->updateQty($cart_product_quantity - $product['quantity'], $id_product, $id_product_attribute, false, 'down');
                    } elseif ($product['quantity'] > $cart_product_quantity) {
                        $cart->updateQty($product['quantity'] - $cart_product_quantity, $id_product, $id_product_attribute, false, 'up');
                    }
                }
            }

            //Get products in prestashop cart but not in oyst cart (remove from modal)
            foreach ($cart_products as $cart_product) {
                //Exception on free items, don't remove them
                if ($cart_product['is_gift']) {
                    continue;
                }

                $ids = $cart_product['id_product'].'-'.$cart_product['id_product_attribute'];
                if (!in_array($ids, $oyst_product_list)) {
                    $cart->deleteProduct($cart_product['id_product'], $cart_product['id_product_attribute']);
                }
            }
        } else {
            //Remove all cart items
            foreach ($cart_products as $cart_product) {
                $cart->deleteProduct($cart_product['id_product'], $cart_product['id_product_attribute']);
            }
        }


        if (!empty($data['coupons'])) {
            $cart_rules = $cart->getCartRules();
            $cart_rule_codes = array();
            foreach ($cart_rules as $cart_rule) {
                if (!empty($cart_rule['code'])) {
                    $cart_rule_codes[] = $cart_rule['code'];
                }
            }

            foreach ($data['coupons'] as $coupon) {
                //Check if the coupon is not already in cart
                if (!in_array($coupon['code'], $cart_rule_codes)) {
                    if (($cart_rule_obj = new CartRule(CartRule::getIdByCode($coupon['code']))) && Validate::isLoadedObject($cart_rule_obj)) {
                        if ($error = $cart_rule_obj->checkValidity($context, false, true)) {
                            if (empty($error)) {
                                $error_msg = 'Unknown error';
                            } else {
                                $error_msg = $error;
                            }
                            $errors['invalid_coupons'][] = array(
                                'code' => $data['discount_coupon'],
                                'error' => $error_msg,
                            );
                        } else {
                            $cart->addCartRule($cart_rule_obj->id);
                        }
                    } else {
                        $errors['invalid_coupons'][] = array(
                            'code' => $data['discount_coupon'],
                            'error' => 'Code node found',
                        );
                    }
                }
            }
        }

        //Customer & address
        $id_customer = 0;
        $id_address_delivery = 0;

        $is_fake_user = false;
        if (empty($data['user']) || $data['user']['email'] == 'no-reply@oyst.com') {
            $is_fake_user = true;
        }

        //First, search customer (id, email)
        //If found => set customer id to cart and check his addresses
        if (!$is_fake_user) {
            $customer_service = CustomerService::getInstance();
            $finded_customer = $customer_service->searchCustomer($data['user']);

            if (!empty($finded_customer['customer_obj'])) {
                $id_customer = $finded_customer['customer_obj']->id;
            }
        }

        $current_delivery_address_obj = null;
        if (!empty($cart->id_address_delivery)) {
            $current_delivery_address_obj = new Address($cart->id_address_delivery);
            if (!Validate::isLoadedObject($current_delivery_address_obj)) {
                $current_delivery_address_obj = null;
            } else {
                $id_address_delivery = $current_delivery_address_obj->id;
            }
        }

        //Create delivery address if not exists
        if (!empty($data['shipping']['address'])) {
            $address_service = AddressService::getInstance();
            $object_service = ObjectService::getInstance();

            //Search if it's fake address and fake address already exists
            if ($is_fake_user) {
                $fake_address = $address_service->getFakeAddress();
            }

            if (!empty($fake_address)) {
                $id_address_delivery = $fake_address->id;
            } else {
                $data['shipping']['address'] = $address_service->formatAddressForPrestashop($data['shipping']['address']);

                //If user is empty, so shipping address is a fake address
                if ($is_fake_user) {
                    $data['shipping']['address']['alias'] = AddressService::OYST_FAKE_ADDR_ALIAS;
                }
                //If address defined in data and exists in customer addresses
                if (!empty($finded_customer['addresses'])) {
                    //Search with address informations
                    $id_address_delivery = $address_service->findExistentAddress($finded_customer['addresses'], $data['shipping']['address']);
                } else {
                    //Else, search if it's the cart address
                    if (!empty($current_delivery_address_obj)) {
                        //Transform object to array with json encode/decode and compare it to oyst delivery_address
                        $current_delivery_address = json_decode(json_encode($current_delivery_address_obj), true);
                        $current_delivery_address['id_address'] = $current_delivery_address['id'];
                        $id_address_delivery = $address_service->findExistentAddress([$current_delivery_address], $data['shipping']['address']);
                    }
                }

                //No address, create it or update Oyst address
                if (empty($id_address_delivery)) {
                    if (!empty($finded_customer['addresses'])) {
                        //If customer was found, get Oyst address
                        $oyst_address = null;
                        foreach ($finded_customer['addresses'] as $address) {
                            if ($address['alias'] == AddressService::OYST_CART_ADDR) {
                                $oyst_address = $address;
                                break;
                            }
                        }

                        //If customer have oyst address, update it
                        if (!empty($oyst_address)) {
                            $result = $object_service->updateObject('Address', $data['shipping']['address'], $oyst_address['id_address']);
                        } else {
                            $result = $object_service->createObject('Address', $data['shipping']['address']);
                        }
                        $id_address_delivery = $result['object']->id;
                    } else {
                        //Adresse not found, check if current adresse is not link to a customer, if true => update it
                        if (!empty($current_delivery_address_obj) && $current_delivery_address_obj->id_customer == 0 && $current_delivery_address_obj->alias != AddressService::OYST_FAKE_ADDR_ALIAS) {
                            $result = $object_service->updateObject('Address', $data['shipping']['address'], $current_delivery_address_obj->id);
                        } else {
                            $result = $object_service->createObject('Address', $data['shipping']['address']);
                        }
                        if (empty($result['errors'])) {
                            if (!empty($id_customer)) {
                                $result['object']->id_customer = $id_customer;
                                $result['object']->save();
                            }
                            $id_address_delivery = $result['id'];
                        } else {
                            $errors['address_delivery'] = $result['errors'];
                        }
                    }
                }
            }
        }

        $cart->id_address_delivery = $cart->id_address_invoice = $id_address_delivery;

        //Get oyst shipment
        if (!empty($data['shipping']['method_applied']['reference'])) {
            $carrier = Carrier::getCarrierByReference($data['shipping']['method_applied']['reference']);
        } else {
            $carrier = new Carrier($this->getCartDefaultShipmentId($cart));
        }

        //Carrier
        if (Validate::isLoadedObject($carrier)) {
            $cart->id_carrier = $carrier->id;
            $delivery_option = $cart->getDeliveryOption();
            $delivery_option[$cart->id_address_delivery] = $cart->id_carrier .",";
            $cart->setDeliveryOption($delivery_option);
        } else {
            $errors[] = 'Carrier '.$data['shipping']['method_applied']['reference'].' not founded';
        }

        //Messages
        if (!empty($data['messages'])) {
            foreach ($data['messages'] as $message) {
                switch ($message['type']) {
                    case 'gift':
                        $cart->gift_message = $message['content'];
                        break;

                    case 'order':
                        //TODO create order message
                        break;
                }
            }
        }

        try {
            $cart->save();
        } catch (Exception $e) {
            $errors['cart'] = $e->getMessage();
        }

        $cart->setNoMultishipping();

        CartRule::autoAddToCart();
        CartRule::autoRemoveFromCart();

        return [
            'cart' => $cart,
            'errors' => $errors,
        ];
    }

    public function getCartDefaultShipmentId($cart)
    {
        $delivery_option = $cart->getDeliveryOption();
        if (!empty($delivery_option[$cart->id_address_delivery])) {
            $tmp = explode(',', $delivery_option[$cart->id_address_delivery]);
            $selected_carrier_id = $tmp[0];
        } else {
            //Get first carrier if no one match with preferences
            if (!empty($carriers[0])) {
                $selected_carrier_id = $carriers[0];
            } else {
                $selected_carrier_id = 0;
            }
        }
        return $selected_carrier_id;
    }
}
