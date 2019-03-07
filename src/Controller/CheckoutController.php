<?php

namespace Oyst\Controller;

use Address;
use Db;
use Cart;
use Oyst;
use Shop;
use Carrier;
use Context;
use Country;
use CartRule;
use Currency;
use Validate;
use Exception;
use Configuration;
use Oyst\Classes\Notification;
use Oyst\Services\AddressService;
use Oyst\Services\CartService;
use Oyst\Services\ObjectService;
use Oyst\Services\CustomerService;
use Oyst\Controller\VersionCompliance\Helper;
use Oyst\Services\VersionCompliance\Helper as ServicesHelper;

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
                $this->respondError(400, 'Error while get cart : '.print_r($response['errors'], true));
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
            $id_oyst = $params['url']['id'];
            if (!empty($params['data'])) {
                if (isset($params['data']['internal_id'])) {
                    $cart_id = $params['data']['internal_id'];
                } else {
                    $cart_id = Notification::getCartIdByOystId($id_oyst);
                }
                $cart = new Cart($cart_id);
                if (Validate::isLoadedObject($cart)) {
                    $data = $params['data'];

                    $errors = [];
                    $context = Context::getContext();

                    $context->cart = $cart;
                    $context->currency = new Currency($cart->id_currency);
                    $context->shop = new Shop($cart->id_shop);

                    if (!empty($id_oyst)) {
                        try {
                            $helper = new Helper();
                            $helper->saveNotification($id_oyst, $cart->id, Notification::WAITING_STATUS);
                        } catch (Exception $e) {
                            //Error on notification creation
                        }
                    }
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
                                    $result = $object_service->createObject('Address', $data['shipping']['address']);
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

                    if (!empty($id_customer)) {
                        $cart->id_customer = $id_customer;
                    }
                    if (!empty($id_address_delivery)) {
                        //If new address != current address and current address has no customer => remove current address
                        if ($id_address_delivery != $cart->id_address_delivery && !empty($current_delivery_address_obj) && $current_delivery_address_obj->id_customer == 0 && $current_delivery_address_obj->alias != AddressService::OYST_FAKE_ADDR_ALIAS) {
                            $current_delivery_address_obj->delete();
                        }
                    }
                    $cart->id_address_delivery = $cart->id_address_invoice = $id_address_delivery;

                    //Carrier
                    if (!empty($data['shipping']['method_applied']['reference'])) {
                        $carrier = Carrier::getCarrierByReference($data['shipping']['method_applied']['reference']);
                        if (Validate::isLoadedObject($carrier)) {
                            $cart->id_carrier = $carrier->id;
                            $cart->setDeliveryOption(array($cart->id_address_delivery => $carrier->id.','));
                            // $cart->delivery_option = json_encode(array($cart->id_address_delivery => $cart->id_carrier.','));
                        } else {
                            $errors[] = 'Carrier '.$data['shipping']['method_applied']['reference'].' not founded';
                        }
                        //TODO Manage access point here (with module exception etc)
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

                    CartRule::autoAddToCart();
                    CartRule::autoRemoveFromCart();

                    if (!empty($errors)) {
                        $this->respondError(400, 'Error while updating cart : '.print_r($errors, true));
                    }

                    $response = CartService::getInstance()->getCart($cart->id);

                    //Remove id_address_delivery if it's john doe
                    if (!empty($cart->id_address_delivery)) {
                        $fake_address = $address_service->getFakeAddress();
                        if (!empty($fake_address) && $fake_address->id == $cart->id_address_delivery) {
                            $cart->id_address_delivery = $cart->id_address_invoice = 0;

                            try {
                                $cart->save();
                            } catch (Exception $e) {
                                $errors['cart'] = $e->getMessage();
                            }
                        }
                    }

                    if (empty($response['errors'])) {
                        $response = array_merge($response, $returned_errors);
                        $this->respondAsJson($response);
                    } else {
                        $this->respondError(400, 'Error while getting cart informations : '.print_r($response['errors'], true));
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
}
