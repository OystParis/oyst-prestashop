<?php

namespace Oyst\Controller;

use Address;
use Cart;
use Carrier;
use Country;
use CartRule;
use Configuration;
use Currency;
use Context;
use Exception;
use Oyst\Controller\VersionCompliance\Helper;
use Oyst\Services\AddressService;
use Validate;
use Oyst\Classes\Notification;
use Oyst\Services\CartService;

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

                    if (!empty($id_oyst)) {
                        try {
                            $helper = new Helper();
                            $helper->saveNotification($id_oyst, $cart->id, Notification::WAITING_STATUS);
                        } catch (Exception $e) {
                            //Error on notification creation
                        }
                    }

                    //Update cart from oyst data to avoid malicious changes
                    $update_result = CartService::getInstance()->updateCart($cart, $params['data']);

                    if (!empty($update_result['errors']['invalid_coupons'])) {
                        $returned_errors['invalid_coupons'] = true;
                        unset($update_result['errors']['invalid_coupons']);
                    }

                    if (!empty($update_result['errors'])) {
                        $this->respondError(400, 'Error while updating cart : '.print_r($update_result['errors'], true));
                    }

                    $response = CartService::getInstance()->getCart($cart->id);

                    $context = Context::getContext();
                    $cart = $context->cart;
                    //Remove id_address_delivery if it's john doe
                    if (!empty($cart->id_address_delivery)) {
                        $fake_address = AddressService::getInstance()->getFakeAddress();
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
                        if (Configuration::hasKey('OYST_HIDE_ERRORS') && Configuration::get('OYST_HIDE_ERRORS')) {
                            ob_clean();
                        }
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
