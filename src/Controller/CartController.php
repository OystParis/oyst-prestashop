<?php

namespace Oyst\Controller;

use Address;
use Carrier;
use Cart;
use Configuration;
use Context;
use Currency;
use Customer;
use Db;
use Exception;
use Language;
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
                    $context = Context::getContext();
                    $response['cart'] = $cart;
                    $response['products'] = $cart->getProducts(true);
                    $carriers = array();
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
            $cart = new Cart((int)$params['url']['id']);
            if (Validate::isLoadedObject($cart)) {
                if (!empty($params['data']['id_carrier'])) {
                    $cart->id_carrier = $params['data']['id_carrier'];

                    //TODO Manage access point here (with module exception etc)
                }

                //TODO Manage different address delivery and address invoice
                $errors = [];
                $id_customer = 0;
                $id_address = 0;
                //First, search customer (id, email, phone)
                //If found => set customer id to cart and check if address
                if (!empty($params['data']['customer'])) {
                    $finded_customer = $this->searchCustomer($params['data']['customer']);
                    //If customer is not finded and we have informations for create him => do it
                    if (empty($finded_customer['customer_obj'])) {
                        $result = $this->createObject('Customer', $params['data']);
                        $id_customer = $result['id'];
                        $errors['customer'] = $result['errors'];
                    } else {
                        //If customer exists, but no address => Create it
                        $id_customer = $finded_customer['customer_obj'];

                        //If address defined in data and exists in customer addresses
                        if (!empty($finded_customer['addresses'])) {
                            if (!empty($params['data']['address']['id_address'])) {
                                foreach ($finded_customer['addresses'] as $address) {
                                    if ($address['id_address'] == $params['data']['address']['id_address']) {
                                        $id_address = $address['id_address'];
                                    }
                                }
                            }
                            //If address not find, use first has default
                            if (empty($id_address)) {
                                $id_address = $finded_customer['addresses'][0]['id_address'];
                            }
                        } else if(!empty($params['data']['address'])) {
                            //No address, create it
                            $result = $this->createObject('Address', $params['data']['address']);
                            $id_address = $result['id'];
                            $errors['address'] = $result['errors'];
                        }
                    }
                }

                if (!empty($id_customer)) {
                    $cart->id_customer = $id_customer;
                }

                if (!empty($id_address)) {
                    $cart->id_address_delivery = $id_address;
                    $cart->id_address_invoice = $id_address;
                }

                try {
                    $cart->save();
                } catch (Exception $e) {
                    $errors['cart'] = $e->getMessage();
                }

                if (!empty($errors)) {
                    $this->respondError(400, $errors);
                }

                if (!empty($params['data']['finalize'])) {
                    $this->createOrderFromCart($params);
                }
                $this->getCart($params);
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
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

    public function searchCustomer($customer_infos)
    {
        $id_lang_fr = Language::getIdByIso('fr');

        //Search on id
        if (!empty($customer_infos['id_customer'])) {
            $customer = new Customer((int)$customer_infos['id_customer']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        //Search on email
        if (empty($addresses) && !empty($customer_infos['email'])) {
            $customer = new Customer();
            $customer->getByEmail($customer_infos['email']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        //Search on phone number
        if (empty($addresses) && !empty($customer_infos['phone'])) {
            $id_customer = Db::getInstance()->getValue("SELECT `c`.`id_customer` 
                FROM `"._DB_PREFIX_."customer` `c`
                INNER JOIN `"._DB_PREFIX_."address` `a` ON `c`.`id_customer` = `a`.`id_customer`
                WHERE `a`.`phone` LIKE '".$customer_infos['phone']."' OR `a`.`phone_mobile` LIKE '".$customer_infos['phone']."'
                ORDER BY `c`.`date_upd` DESC");

            $customer = new Customer($id_customer);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        $results = array();

        if (Validate::isLoadedObject($customer)) {
            $results['customer_obj'] = $customer;
        }

        if (!empty($addresses)) {
            $results['addresses'] = $addresses;
        }
        return $results;
    }

    public function getRequiredFields($object_name)
    {
        $required_fields = array();
        foreach ($object_name::$definition['fields'] as $field_name => $field) {
            if (isset($field['required']) && $field['required']) {
                $required_fields[] = $field_name;
            }
        }
        return $required_fields;
    }

    public function createObject($object_name, $fields)
    {
        $errors = [];
        $id = 0.
        $object_required_fields = $this->getRequiredFields($object_name);
        foreach ($object_required_fields as $object_required_field) {
            if (!isset($fields[$object_required_field])) {
                $errors[] = 'Missing field '.$object_required_field;
            }
        }
        if (empty($errors)) {
            $object = new $object_name();
            foreach ($fields as $field_name => $value) {
                if (in_array($field_name, array('firstname', 'lastname'))) {
                    $value = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $value);
                }
                if (isset($object_name::$definition['fields'][$field_name]['size'])) {
                    $value = Tools::substr($value, 0, $object_name::$definition['fields'][$field_name]['size']);
                }
                $object->$field_name = $value;
            }

            //Exception management
            if ($object_name == 'Customer') {
                if (version_compare(_PS_VERSION_, '1.5.4.0', '>=')) {
                    $object->id_lang = Configuration::get('PS_LANG_DEFAULT');
                }
                //TODO Ask: Générer pass dans connecteur ou envoyer un pass vide ?
                $password = Tools::passwdGen();

                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $object->passwd = Tools::encrypt($password);
                } else {
                    $object->passwd = Tools::hash($password);
                }
            }

            try  {
                $object->add();
                $id = $object->id;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }

        }
        return array(
            'id' => $id,
            'errors' => $errors,
        );
    }
}
