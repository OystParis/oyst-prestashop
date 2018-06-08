<?php

namespace Oyst\Controller;

use Address;
use Carrier;
use Cart;
use CartRule;
use Configuration;
use Context;
use Country;
use Currency;
use Customer;
use Exception;
use Message;
use Order;
use Oyst;
use Oyst\Classes\Notification;
use Oyst\Services\CustomerService;
use Oyst\Services\ObjectService;
use Oyst\Services\OrderService;
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

    public function getCart($params, $display = true)
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
                    $response['available_carriers'] = $carriers;

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
                    if ($display) {
                        $this->respondAsJson($response);
                    } else {
                        return $response;
                    }
                } catch(Exception $e) {
                    $this->respondError(400, $e->getMessage());
                }
            } else {
                $this->respondError(400, 'Bad id_cart');
            }
        } else {
            $this->respondError(400, 'id_cart is missing');
        }
        return array();
    }

    public function updateCart($params)
    {
        $returned_errors = array();
        if (!empty($params['url']['id'])) {
            $cart = new Cart((int)$params['url']['id']);
            if (Validate::isLoadedObject($cart)) {
                $errors = [];

                //Products
                if (!empty($params['data']['products'])) {
                    $cart_products = $cart->getProducts(false, false, null, false);
                    foreach ($params['data']['products'] as $product) {
                        if (isset($product['quantity']) && isset($product['id_product'])) {
                            if (!isset($product['id_product_attribute'])) {
                                $product['id_product_attribute'] = 0;
                            }
                        }
                        //TODO Manage customization
                        if ($product['quantity'] <= 0) {
                            $cart->deleteProduct($product['id_product'], $product['id_product_attribute']);
                        } else {
                            $cart_product_quantity = 0;
                            foreach ($cart_products as $cart_product) {
                                if ($cart_product['id_product'] == $product['id_product'] && $cart_product['id_product_attribute'] == $product['id_product_attribute']) {
                                    $cart_product_quantity = $cart_product['cart_quantity'];
                                }
                            }
                            if ($product['quantity'] < $cart_product_quantity) {
                                $cart->updateQty($cart_product_quantity-$product['quantity'], $product['id_product'], $product['id_product_attribute'], false, 'down');
                            } elseif ($product['quantity'] > $cart_product_quantity) {
                                $cart->updateQty($product['quantity']-$cart_product_quantity, $product['id_product'], $product['id_product_attribute'], false, 'up');
                            }
                        }
                    }
                }

                if (!empty($params['data']['discount_coupon'])) {
                    $context = Context::getContext();
                    $context->cart = $cart;
                    $context->currency = new Currency($cart->id_currency);
                    $context->shop = new Shop($cart->id_shop);

                    if (($cart_rule = new CartRule(CartRule::getIdByCode($params['data']['discount_coupon']))) && Validate::isLoadedObject($cart_rule)) {
                        if ($error = $cart_rule->checkValidity($context, false, true)) {
                            if (empty($error)) {
                                $error_msg = 'Unknown error';
                            } else {
                                $error_msg = $error;
                            }
                            $returned_errors['invalid_coupons'][] = array(
                                'code' => $params['data']['discount_coupon'],
                                'error' => $error_msg,
                            );
                        } else {
                            $cart->addCartRule($cart_rule->id);
                        }
                    } else {
                        $returned_errors['invalid_coupons'][] = array(
                            'code' => $params['data']['discount_coupon'],
                            'error' => 'Code node found',
                        );
                    }
                }

                //Customer & address
                //TODO Manage different address delivery and address invoice
                $id_customer = 0;
                $id_address = 0;
                //First, search customer (id, email)
                //If found => set customer id to cart and check if address
                if (!empty($params['data']['customer'])) {
                    $customer_service = CustomerService::getInstance();
                    $object_service = ObjectService::getInstance();
                    $finded_customer = $customer_service->searchCustomer($params['data']['customer']);
                    //If customer is not finded and we have informations for create him => do it
                    if (empty($finded_customer['customer_obj'])) {
                        $result = $object_service->createObject('Customer', $params['data']['customer']);
                        $id_customer = $result['id'];
                        if (!empty($result['errors'])) {
                            $errors['customer'] = $result['errors'];
                        }
                    } else {
                        //If customer exists, but no address => Create it
                        $id_customer = $finded_customer['customer_obj']->id;

                        //If address defined in data and exists in customer addresses
                        if (!empty($finded_customer['addresses'])) {
                            if (!empty($params['data']['address']['id_address'])) {
                                foreach ($finded_customer['addresses'] as $address) {
                                    if ($address['id_address'] == $params['data']['address']['id_address']) {
                                        $id_address = $address['id_address'];
                                    }
                                }
                            }

                            //If no address find by id, search with address informations
                            if (empty($id_address)) {
                                $fields_to_find = array(
                                    'firstname',
                                    'lastname',
                                    'address1',
                                    'postcode',
                                    'city',
                                );

                                //If fields required for searching are all present in data
                                if (count(array_diff($fields_to_find, array_keys($params['data']['address']))) == 0) {
                                    foreach ($finded_customer['addresses'] as $address) {
                                        $address_finded = true;
                                        foreach ($fields_to_find as $field_to_find) {
                                            $address_finded &= ($address[$field_to_find] == $params['data']['address'][$field_to_find]);
                                        }
                                        if ($address_finded) {
                                            $id_address = $address['id_address'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($id_customer) && empty($id_address) && !empty($params['data']['address'])) {
                        //No address, create it
                        if (!empty($params['data']['address']['country'])) {
                            if ($id_country = Country::getByIso($params['data']['address']['country'])) {
                                $params['data']['address']['id_country'] = $id_country;
                            } else {
                                $errors[] = "Country code not exists";
                            }
                        }
                        $result = $object_service->createObject('Address', $params['data']['address']);
                        if (empty($result['errors'])) {
                            $result['object']->id_customer = $id_customer;
                            $result['object']->save();
                            $id_address = $result['id'];
                        } else {
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

                //Carrier
                if (!empty($params['data']['id_carrier_reference'])) {
                    $carrier = Carrier::getCarrierByReference($params['data']['id_carrier_reference']);
                    if (Validate::isLoadedObject($carrier)) {
                        $cart->id_carrier = $carrier->id;
                        $cart->delivery_option = json_encode(array($cart->id_address_delivery => $cart->id_carrier.','));
                    } else {
                        $errors[] = 'Carrier not founded';
                    }
                    //TODO Manage access point here (with module exception etc)
                }

                try {
                    $cart->save();
                } catch (Exception $e) {
                    $errors['cart'] = $e->getMessage();
                }

                CartRule::autoAddToCart();
                CartRule::autoRemoveFromCart();

                if (!empty($errors)) {
                    $this->respondError(400, $errors);
                }

                if (!empty($params['data']['finalize'])) {
                    $this->createOrderFromCart($params);
                }
                $response = $this->getCart($params, false);
                $response = array_merge($response, $returned_errors);
                $this->respondAsJson($response);
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
