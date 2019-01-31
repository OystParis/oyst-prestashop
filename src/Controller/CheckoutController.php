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
					//If no items in cart = delete cart
                	if (empty($data['items'])) {
                		$cart->delete();
                		$context->cart = null;
						$this->respondAsJson(array());
						exit;
					}

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
					$cart_products = $cart->getProducts(false, false, null, false);
					foreach ($data['items'] as $product) {
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

                        //If customer found, search addresses
                        if (!empty($finded_customer['customer_obj'])) {
                            $id_customer = $finded_customer['customer_obj']->id;
                        }
                    }

                    $current_delivery_address_obj = null;
                    if (!empty($cart->id_address_delivery)) {
                        $current_delivery_address_obj = new Address($cart->id_address_delivery);
                        if (!Validate::isLoadedObject($current_delivery_address_obj)) {
                            $current_delivery_address_obj = null;
                        }
                    }

                    //Create delivery address if not exists
                    if (!empty($data['shipping']['address'])) {

                        $address_service = AddressService::getInstance();
                        $object_service = ObjectService::getInstance();
                        $data['shipping']['address'] = $address_service->formatAddressForPrestashop($data['shipping']['address']);

                        //If user is empty, so shipping address is a fake address
                        if ($is_fake_user) {
                            $data['shipping']['address']['alias'] = 'Fake address';
                        }
                        //If address defined in data and exists in customer addresses
                        if (!empty($finded_customer['addresses'])) {
                            //Search with address informations
                            $id_address_delivery = $address_service->findExistentAddress($finded_customer['addresses'], $data['shipping']['address']);
                        } else {
                            //Else, search if it's the cart address
                            if (!empty($current_delivery_address_obj)) {
                                $current_delivery_address = json_decode(json_encode($current_delivery_address_obj), true);
                                $current_delivery_address['id_address'] = $current_delivery_address['id'];
                                //Transform object to array with json encode/decode and compare it to oyst delivery_address
                                $id_address_delivery = $address_service->findExistentAddress(array($current_delivery_address), $data['shipping']['address']);

                                //If oyst address is different than cart address and cart address was fake address => update it
                                if ($current_delivery_address_obj->alias == 'Fake address' && empty($id_address_delivery)) {
                                    $current_delivery_address_obj = $object_service->updateObject('Address', $data['shipping']['address'], $current_delivery_address['id_address']);
                                    if (!empty($id_customer)) {
                                        $current_delivery_address_obj['object']->id_customer = $id_customer;
                                    }
                                    $current_delivery_address_obj['object']->alias .= ' '.$current_delivery_address_obj['object']->id;
                                    $current_delivery_address_obj['object']->save();

                                    $id_address_delivery = $current_delivery_address_obj['object']->id;
                                }
                            }
                        }

                        //No address, create it
                        if (empty($id_address_delivery)) {
                            $result = $object_service->createObject('Address', $data['shipping']['address']);
                            if (empty($result['errors'])) {
                                if (!empty($id_customer)) {
                                    $result['object']->id_customer = $id_customer;
                                }
                                if ($result['object']->alias != 'Fake address') {
                                    $result['object']->alias .= ' '.$result['object']->id;
                                }
                                $result['object']->save();
                                $id_address_delivery = $result['id'];
                            } else {
                                $errors['address_delivery'] = $result['errors'];
                            }
                        }
                    }

                    if (!empty($id_customer)) {
                        $cart->id_customer = $id_customer;
                    }
                    if (!empty($id_address_delivery)) {
                        //If new address != current address and current address has no customer => remove current address
                        if ($id_address_delivery != $cart->id_address_delivery && !empty($current_delivery_address_obj) && $current_delivery_address_obj->id_customer == 0) {
                            $current_delivery_address_obj->delete();
                        }
                        $cart->id_address_delivery = $id_address_delivery;
                    }

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
            $notification = Notification::getNotificationByOystId($params['data']['id_oyst_order']);

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
            } catch (Exception $e) {
                $this->logger->error('Failed to transform cart '.$cart->id.' into order (Exception : '.$e->getMessage().')');
                $this->respondError(400, 'Exception on order creation : '.$e->getMessage());
            }
        } else {
            $this->respondError(400, 'Bad id_cart');
        }
    }
}
