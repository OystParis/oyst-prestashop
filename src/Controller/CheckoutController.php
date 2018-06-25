<?php

namespace Oyst\Controller;

use Carrier;
use Cart;
use CartRule;
use Configuration;
use Context;
use Country;
use Currency;
use Exception;
use Oyst;
use Oyst\Classes\Notification;
use Oyst\Services\CartService;
use Oyst\Services\CustomerService;
use Oyst\Services\ObjectService;
use Shop;
use Validate;

class CheckoutController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('cart');
    }

    public function getCart($params)
    {
        if (!empty($params['url']['id'])) {
            $response = CartService::getInstance()->getCart($params['url']['id']);
            if (empty($response['errors'])) {
                $this->respondAsJson($response);
            } else {
                $this->respondError(400, $response['errors']);
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
            if (!empty($params['data']['checkout'])) {
                $cart = new Cart((int)$params['url']['id']);
                if (Validate::isLoadedObject($cart)) {
                    $errors = [];
                    $context = Context::getContext();
                    $context->cart = $cart;
                    $context->currency = new Currency($cart->id_currency);
                    $context->shop = new Shop($cart->id_shop);

                    $data = $params['data']['checkout'];
                    //Products
                    if (!empty($data['items'])) {
                        $cart_products = $cart->getProducts(false, false, null, false);
                        foreach ($data['items'] as $product) {
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
                                    $cart->updateQty($cart_product_quantity - $product['quantity'], $product['id_product'], $product['id_product_attribute'], false, 'down');
                                } elseif ($product['quantity'] > $cart_product_quantity) {
                                    $cart->updateQty($product['quantity'] - $cart_product_quantity, $product['id_product'], $product['id_product_attribute'], false, 'up');
                                }
                            }
                        }
                    }

                    if (!empty($data['promotions']['coupons'])) {
                        $cart_rules = $cart->getCartRules();
                        $cart_rule_codes = array();
                        foreach ($cart_rules as $cart_rule) {
                            if (!empty($cart_rule['code'])) {
                                $cart_rule_codes[] = $cart_rule['code'];
                            }
                        }

                        foreach ($data['promotions']['coupons'] as $coupon) {
                            //Check if the coupon is not already in cart
                            if (!in_array($coupon['code'], $cart_rule_codes)) {
                                if (($cart_rule_obj = new CartRule(CartRule::getIdByCode($coupon['code']))) && Validate::isLoadedObject($cart_rule_obj)) {
                                    if ($error = $cart_rule_obj->checkValidity($context, false, true)) {
                                        if (empty($error)) {
                                            $error_msg = 'Unknown error';
                                        } else {
                                            $error_msg = $error;
                                        }
                                        $returned_errors['invalid_coupons'][] = array(
                                            'code' => $data['discount_coupon'],
                                            'error' => $error_msg,
                                        );
                                    } else {
                                        $cart->addCartRule($cart_rule_obj->id);
                                    }
                                } else {
                                    $returned_errors['invalid_coupons'][] = array(
                                        'code' => $data['discount_coupon'],
                                        'error' => 'Code node found',
                                    );
                                }
                            }
                        }
                    }

                    //Customer & address
                    //TODO Manage different address delivery and address invoice
                    $id_customer = 0;
                    $id_address = 0;
                    //First, search customer (id, email)
                    //If found => set customer id to cart and check his addresses
                    if (!empty($data['user'])) {
                        $customer_service = CustomerService::getInstance();
                        $object_service = ObjectService::getInstance();
                        $finded_customer = $customer_service->searchCustomer($data['user']);
                        //If customer is not finded and we have informations for create him => do it
                        if (empty($finded_customer['customer_obj'])) {
                            $result = $object_service->createObject('Customer', $data['user']);
                            $id_customer = $result['id'];
                            if (!empty($data['user']['id_oyst'])) {
                                Oyst\Classes\Customer::createOystCustomerLink($id_customer, $data['user']['id_oyst']);
                            }
                            if (!empty($result['errors'])) {
                                $errors['customer'] = $result['errors'];
                            }
                        } else {
                            //If customer exists, but no address => Create it
                            $id_customer = $finded_customer['customer_obj']->id;

                            //If address defined in data and exists in customer addresses
                            if (!empty($finded_customer['addresses'])) {
                                //Search with address informations
                                if (empty($id_address)) {
                                    $fields_to_find = array(
                                        'firstname',
                                        'lastname',
                                        'address1',
                                        'postcode',
                                        'city',
                                    );

                                    //If fields required for searching are all present in data
                                    if (count(array_diff($fields_to_find, array_keys($data['shipping']['address']))) == 0) {
                                        foreach ($finded_customer['addresses'] as $address) {
                                            $address_finded = true;
                                            foreach ($fields_to_find as $field_to_find) {
                                                $address_finded &= ($address[$field_to_find] == $data['shipping']['address'][$field_to_find]);
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

                        if (!empty($id_customer) && empty($id_address) && !empty($data['shipping']['address'])) {
                            //No address, create it
                            if (!empty($data['shipping']['address']['country'])) {
                                if ($id_country = Country::getByIso($data['shipping']['address']['country'])) {
                                    $data['shipping']['address']['id_country'] = $id_country;
                                } else {
                                    $errors[] = "Country code not exists";
                                }
                            }
                            $result = $object_service->createObject('Address', $data['shipping']['address']);
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
                    if (!empty($data['shipping']['carrier_applied'])) {
                        $carrier = Carrier::getCarrierByReference($data['shipping']['carrier_applied']);
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

                    $response = CartService::getInstance()->getCart($cart->id);
                    if (empty($response['errors'])) {
                        $response = array_merge($response, $returned_errors);
                        $this->respondAsJson($response);
                    } else {
                        $this->respondError(400, $response['errors']);
                    }
                } else {
                    $this->respondError(400, 'Bad id_cart');
                }
            } else {
                $this->respondError(400, 'data is missing');
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
