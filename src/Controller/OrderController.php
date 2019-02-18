<?php

namespace Oyst\Controller;

use Address;
use Cart;
use Configuration;
use Context;
use Exception;
use Order;
use OrderHistory;
use OrderSlip;
use Oyst;
use Oyst\Classes\Notification;
use Oyst\Services\AddressService;
use Oyst\Services\ObjectService;
use Oyst\Services\OrderService;
use Oyst\Services\OystStatusService;
use Validate;

class OrderController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('order');
    }

    public function getOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystId($params['url']['id']);
            $response = OrderService::getInstance()->getOrder($id_order);
            $this->respondAsJson($response);
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function updateOrder($params)
    {
        if (!empty($params['url']['id'])) {
            $id_order = Notification::getOrderIdByOystId($params['url']['id']);
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                $result = array();
                if (!empty($params['data']['id_order_state'])) {
                    if ($order->current_state != $params['data']['id_order_state']) {
                        $order->setCurrentState($params['data']['id_order_state']);
                        $result['change_order_state'] = array('success' => true);
                    } else {
                        $result['change_order_state'] = array('error' => 'The order already has this status');
                    }
                }

                $this->respondAsJson($result);
            } else {
                $this->respondError(400, 'Bad id_order');
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }

    public function createOrder($params)
    {
        if (!empty($params['data'])) {
            $notification = Notification::getNotificationByOystId($params['data']['oyst_id']);
            if (empty($notification)) {
                $this->respondError(400, 'Notification not found');
            } else {
                if ($notification->isAlreadyStarted()) {
                    $this->respondError(400, 'Order already on creation');
                } elseif ($notification->isAlreadyFinished()) {
                    $this->respondError(400, 'Order already created');
                } else {
                    $cart = new Cart($notification->cart_id);
                    if (Validate::isLoadedObject($cart)) {
                        $notification->start();
                        $oyst = new Oyst();

                        $object_service = ObjectService::getInstance();
                        //Create user if not exists
                        if (empty($cart->id_customer)) {
                            if (empty($params['data']['user'])) {
                                $this->respondError(400, 'User is empty');
                            }
                            $result = $object_service->createObject('Customer', $params['data']['user']);
                            $cart->id_customer = $result['id'];
                            $cart->secure_key = $result['object']->secure_key;

                            if (!empty($params['data']['user']['id_oyst']) && !empty($cart->id_customer)) {
                                Oyst\Classes\OystCustomer::createOystCustomerLink($cart->id_customer, $params['data']['user']['id_oyst']);
                            }

                            if (!empty($result['errors'])) {
                                $errors['customer'] = $result['errors'];
                            }
                        }

                        if (empty($params['data']['shipping']['address'])) {
                            $this->respondError(400, 'Delivery address not in data');
                        }

                        $address = new Address($cart->id_address_delivery);
                        $address_service = AddressService::getInstance();
                        if (Validate::isLoadedObject($address)) {

                            $params['data']['shipping']['address'] = $address_service->formatAddressForPrestashop($params['data']['shipping']['address']);

                            //Check if cart address is the same as oyst delivery address
                            $delivery_address = json_decode(json_encode($address), true);
                            $delivery_address['id_address'] = $delivery_address['id'];
                            $id_address_delivery = $address_service->findExistentAddress(array($delivery_address), $params['data']['shipping']['address']);

                            //If addresses are different => create oyst address
                            if (empty($id_address_delivery)) {
                                //If cart address has no customer, remove it
                                if ($address->id_customer == 0) {
                                    $address->delete();
                                }

                                $result = $object_service->createObject('Address', $params['data']['shipping']['address']);
                                if (empty($result['errors'])) {
                                    $result['object']->id_customer = $cart->id_customer;
                                    $result['object']->alias .= ' '.$result['object']->id;
                                    try {
                                        $result['object']->save();
                                    } catch (Exception $e) {
                                        $this->respondError(400, 'Error while creating shipping address : '.$e->getMessage());
                                    }
                                    $delivery_address = json_decode(json_encode($result['object']), true);
                                    $delivery_address['id_address'] = $delivery_address['id'];
                                    $id_address_delivery = $result['id'];
                                } else {
                                    $this->respondError(400, 'Error on address delivery creation : '.$result['errors']);
                                }
                            } else {
                                //If same addresses, set address to customer
                                $address->id_customer = $cart->id_customer;
                                try {
                                    $address->update();
                                } catch (Exception $e) {
                                    $this->respondError(400, 'Error while updating shipping address : '.$e->getMessage());
                                }
                            }
                        } else {
                            //If cart address doesn't exists
                            $result = $object_service->createObject('Address', $params['data']['shipping']['address']);
                            if (empty($result['errors'])) {
                                $result['object']->id_customer = $cart->id_customer;
                                $result['object']->alias .= ' '.$result['object']->id;
                                try {
                                    $result['object']->save();
                                } catch (Exception $e) {
                                    $this->respondError(400, 'Error while creating shipping address : '.$e->getMessage());
                                }
                                $delivery_address = json_decode(json_encode($result['object']), true);
                                $delivery_address['id_address'] = $delivery_address['id'];
                                $id_address_delivery = $result['id'];
                            } else {
                                $this->respondError(400, 'Error on address delivery creation : '.$result['errors']);
                            }
                        }

                        $cart->id_address_delivery = $id_address_delivery;

                        if (empty($params['data']['billing']['address'])) {
                            $this->respondError(400, 'Billing address not in data');
                        }

                        //Checked if invoice address is same as delivery address
                        $params['data']['billing']['address'] = $address_service->formatAddressForPrestashop($params['data']['billing']['address']);
                        $id_address_invoice = $address_service->findExistentAddress(array($delivery_address), $params['data']['billing']['address']);

                        if (empty($id_address_invoice)) {
                            $result = $object_service->createObject('Address', $params['data']['billing']['address']);
                            if (empty($result['errors'])) {
                                $result['object']->id_customer = $cart->id_customer;
                                $result['object']->alias .= ' '.$result['object']->id;
                                try {
                                    $result['object']->save();
                                } catch (Exception $e) {
                                    $this->respondError(400, 'Error while creating billing address : '.$e->getMessage());
                                }
                                $id_address_invoice = $result['id'];
                            } else {
                                $this->respondError(400, 'Error on invoice address creation : '.$result['errors']);
                            }
                        }
                        $cart->id_address_delivery = $id_address_delivery;
                        $cart->id_address_invoice = $id_address_invoice;
                        try {
                            $cart->save();
                        } catch (Exception $e) {
                            $this->respondError(400, 'Error while updating cart : '.$e->getMessage());
                        }

                        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
                        try {
                            // Tricks for hookActionEmailSendBefore to not send order creation email
                            $context = Context::getContext();
                            $context->oyst_skip_mail = true;
                            //Set notification into context for reference usage on hookActionEmailSendBefore
                            $context->oyst_current_notification = $notification;

                            if ($oyst->validateOrder($cart->id, Configuration::get('OYST_OS_PAY_WAITING_TO_CAPTURE'), $total, $oyst->displayName, null, array(), (int)$cart->id_currency, false, $cart->secure_key)) {
                                $notification->complete($oyst->currentOrder);
                                // Set id_cart to 0 to avoid cart deletion on front office
                                $order = new Order($oyst->currentOrder);
                                if (Validate::isLoadedObject($order)) {
                                    $order->id_cart = 0;
                                    $order->update();
                                }
                                $this->respondAsJson(OrderService::getInstance()->getOrder($oyst->currentOrder));
                            } else {
                                $this->respondError(400, 'Order creation failed');
                            }
                        } catch (Exception $e) {
                            $this->logger->error('Failed to transform cart '.$cart->id.' into order (Exception : '.$e->getMessage().')');
                            $this->respondError(500, 'Exception on order creation : '.$e->getMessage());
                        }
                    } else {
                        $this->respondError(400, 'Bad id_cart');
                    }
                }
            }
        } else {
            $this->respondError(400, 'data is empty');
        }
    }

    public function changeStatus($params)
    {
        if (!empty($params['url']['id']) && $params['data']['oystOrder']['status']['code']) {
            $prestashop_status_name = OystStatusService::getInstance()->getPrestashopStatusFromOystStatus($params['data']['oystOrder']['status']['code']);
            if (Configuration::hasKey($prestashop_status_name)) {
                $notification = Notification::getNotificationByOystId($params['url']['id']);
                if (Validate::isLoadedObject($notification)) {
                    try {
                        $order = new Order($notification->order_id);
                        if (Validate::isLoadedObject($order)) {
                            if ($order->getCurrentState() != Configuration::get($prestashop_status_name)) {
                                // If status oyst_payment_captured => send order email to customer
                                $history = new OrderHistory();
                                $history->id_order = $notification->order_id;
                                $history->changeIdOrderState(Configuration::get($prestashop_status_name), $order, true);
                                $history->addWithemail();
                                if ($params['data']['oystOrder']['status']['code'] == 'oyst_payment_captured') {
                                    $notification->sendOrderEmail();
                                    //Set id_cart to order for cart avoid
                                    $order->id_cart = $notification->cart_id;
                                    $order->update();
                                }
                            }
                            $this->respondAsJson(array('success' => 1));
                        } else {
                            $this->respondError(400, 'can\'t load order object');
                        }
                    } catch(Exception $e) {
                        $this->respondError(400, 'fail on status change : '.$e->getMessage());
                    }
                } else {
                    $this->respondError(400, 'notification not found');
                }
            } else {
                $this->respondError(400, 'order status not found');
            }
        } else {
            $this->respondError(400, 'missing order_id or status code is empty');
        }
    }

    public function refundOrder($params)
    {
        if (!empty($params['url']['id'])) {
            if (!empty($params['data']['refund'])) {
                $id_order = Notification::getOrderIdByOystId($params['url']['id']);
                $order = new Order($id_order);

                if (!empty($params['data']['refund']['total'])) {
                    $amount = 0;
                    $amount_choosen = false;
                    $products_list = array();
                    //Get order details
                    foreach ($order->getProductsDetail() as $order_detail) {
                        $products_list[] = array(
                            'id_order_detail' => $order_detail['id_order_detail'],
                            'unit_price' => $order_detail['unit_price_tax_excl'],
                            'quantity' => $order_detail['product_quantity'],
                        );
                    };
                    $shipping_cost = $order->total_shipping_tax_excl;

                    if (OrderSlip::create($order, $products_list, $shipping_cost, $amount, $amount_choosen)) {
                        $this->respondAsJson('Order slip created successfully');
                    } else {
                        $this->respondError(400, 'Order slip creation failed');
                    }
                } elseif($params['data']['refund']['partial']) {
                    //TODO Manage partial refund
                }
            }
        } else {
            $this->respondError(400, 'id_order is missing');
        }
    }
}
