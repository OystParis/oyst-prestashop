<?php

namespace Oyst\Controller;

use Address;
use Carrier;
use Cart;
use Configuration;
use Context;
use Country;
use Customer;
use Db;
use Exception;
use Module;
use Order;
use OrderHistory;
use OrderSlip;
use Oyst;
use Oyst\Classes\Notification;
use Oyst\Services\AddressService;
use Oyst\Services\CartService;
use Oyst\Services\CustomerService;
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

                    $context = Context::getContext();
                    $context->cart = $cart;

                    if (Validate::isLoadedObject($cart)) {
                        $notification->start();
                        $oyst = new Oyst();

                        //Update cart from oyst data to avoid malicious changes
                        $update_result = CartService::getInstance()->updateCart($cart, $params['data']);
                        $cart = $update_result['cart'];

                        $object_service = ObjectService::getInstance();
                        //Create user if not exists
                        if (empty($cart->id_customer)) {
                            if (empty($params['data']['user'])) {
                                $this->respondError(400, 'User is empty');
                            } else {
                                //If customer not set in cart search if not already exists
                                $finded_customer = CustomerService::getInstance()->searchCustomer($params['data']['user']);
                                if (!empty($finded_customer['customer_obj'])) {
                                    $customer = $finded_customer['customer_obj'];
                                } else {
                                    //If not existed, create it
                                    $customer = $object_service->createObject('Customer', $params['data']['user'])['object'];

                                    if (!empty($result['errors'])) {
                                        $errors['customer'] = $result['errors'];
                                    }
                                }
                            }

                            $cart->id_customer = $customer->id;
                            $cart->secure_key = $customer->secure_key;

                            if (!empty($params['data']['user']['id_oyst']) && !empty($cart->id_customer)) {
                                Oyst\Classes\OystCustomer::createOystCustomerLink($cart->id_customer, $params['data']['user']['id_oyst']);
                            }
                        }

                        if (isset($params['data']['user']['newsletter']) && $params['data']['user']['newsletter']) {
                            if (empty($customer)) {
                                $customer = new Customer($cart->id_customer);
                            }
                            if (Validate::isloadedObject($customer)) {
                                $customer->newsletter = true;
                                $customer->save();
                            }
                        }

                        if (empty($params['data']['shipping']['address'])) {
                            $this->respondError(400, 'Delivery address not in data');
                        }

                        $address_service = AddressService::getInstance();
                        $params['data']['shipping']['address'] = $address_service->formatAddressForPrestashop($params['data']['shipping']['address']);

                        $address = new Address($cart->id_address_delivery);

                        if (Validate::isLoadedObject($address)) {

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
                                    $result['object']->alias = AddressService::OYST_CART_ADDR.' '.$result['object']->id;
                                    try {
                                        $result['object']->save();
                                    } catch (Exception $e) {
                                        $this->respondError(400, 'Error while creating shipping address : '.$e->getMessage());
                                    }
                                    $delivery_address = json_decode(json_encode($result['object']), true);
                                    $delivery_address['id_address'] = $delivery_address['id'];
                                    $id_address_delivery = $result['object']->id;
                                } else {
                                    $this->respondError(400, 'Error on address delivery creation : '.print_r($result['errors'], true));
                                }
                            } else {
                                //If same addresses, set address to customer
                                $address->id_customer = $cart->id_customer;
                                $address->alias = AddressService::OYST_CART_ADDR.' '.$address->id;
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
                                $result['object']->alias = AddressService::OYST_CART_ADDR.' '.$result['object']->id;
                                try {
                                    $result['object']->save();
                                } catch (Exception $e) {
                                    $this->respondError(400, 'Error while creating shipping address : '.$e->getMessage());
                                }
                                $delivery_address = json_decode(json_encode($result['object']), true);
                                $delivery_address['id_address'] = $delivery_address['id'];
                                $id_address_delivery = $result['object']->id;
                            } else {
                                $this->respondError(400, 'Error on address delivery creation : '.print_r($result['errors'], true));
                            }
                        }

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
                                $result['object']->alias = AddressService::OYST_CART_ADDR.' '.$result['object']->id;
                                try {
                                    $result['object']->save();
                                } catch (Exception $e) {
                                    $this->respondError(400, 'Error while creating billing address : '.$e->getMessage());
                                }
                                $id_address_invoice = $result['object']->id;
                            } else {
                                $this->respondError(400, 'Error on invoice address creation : '.print_r($result['errors']));
                            }
                        }

                        $cart->id_address_delivery = $id_address_delivery;
                        $cart->id_address_invoice = $id_address_invoice;

                        //Set only one delivery option => The final choice
                        $delivery_option = [];
                        $delivery_option[$cart->id_address_delivery] = $cart->id_carrier .",";
                        $cart->setDeliveryOption($delivery_option);

                        try {
                            $cart->save();
                        } catch (Exception $e) {
                            $this->respondError(400, 'Error while updating cart : '.$e->getMessage());
                        }

                        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
                        try {
                            // Tricks for hookActionEmailSendBefore to not send order creation email
                            $context->oyst_skip_mail = true;
                            //Set notification into context for reference usage on hookActionEmailSendBefore
                            $context->oyst_current_notification = $notification;

                            if (isset($params['data']['pickup_store']['address'])) {
                                $pickup_address = $params['data']['pickup_store']['address'];
                                $pickup_id = $params['data']['pickup_store']['id'];
                                $carrier = new Carrier($cart->id_carrier);
                                $product_code = isset($params['data']['pickup_store']['codeType']) ? pSQL($params['data']['pickup_store']['codeType']) : '';
                                $network = isset($params['data']['pickup_store']['network']) ? pSQL($params['data']['pickup_store']['network']) : '';
                                $oyst_carrier_type = $params['data']['shipping']['method_applied']['type'];
                                $oyst_invoice_address = $params['data']['billing']['address'];
                                $phone = str_replace('+33', '0', $oyst_invoice_address['phone_mobile']);

                                //Manage soflexibilite and soliberte, id order is added on hookActionValidateOrder
                                if (($oyst_carrier_type == 'colissimo_poste' || $oyst_carrier_type == 'colissimo_commerces') &&
                                    (Module::isEnabled('soflexibilite') || Module::isEnabled('soliberte'))) {

                                    //If soflexibilite
                                    if (Module::isEnabled('soflexibilite')) {
                                        $delivery = new \SoFlexibiliteDelivery($cart->id, $cart->id_customer);
                                    } else {
                                        //Else soliberte
                                        $delivery = new \SoLiberteDelivery($cart->id, $cart->id_customer);
                                    }

                                    $delivery->id_point = $pickup_id;
                                    $delivery->firstname = $oyst_invoice_address['firstname'];
                                    $delivery->lastname = $oyst_invoice_address['lastname'];
                                    $delivery->company = $oyst_invoice_address['company'];
                                    $delivery->telephone = $phone;
                                    $delivery->email = $oyst_invoice_address['email'];
                                    $delivery->type = $product_code;
                                    $delivery->libelle = $pickup_address['name'];
                                    $delivery->postcode = $pickup_address['postal_code'];
                                    $delivery->city = $pickup_address['city'];
                                    $delivery->country = $pickup_address['country'];
                                    $delivery->address1 = $pickup_address['street'];
                                    if (property_exists(get_class($delivery), 'reseau')) {
                                        $delivery->reseau = $network;
                                    }

                                    file_put_contents(__DIR__.'/../../../../debug.log', date('Y-m-d H:i:s')." - ".print_r($delivery, true)." \r\n", FILE_APPEND);
                                    $delivery->saveDelivery();
                                }

                                if ($oyst_carrier_type == 'colissimo' && $pickup_id && Module::isEnabled('colissimo')) {

                                    //Load pickup point if exists
                                    $pickup_point = \ColissimoPickupPoint::getPickupPointByIdColissimo($pickup_id);
                                    if (!Validate::isLoadedObject($pickup_point)) {
                                        $id_country = Country::getByIso($pickup_address['country']);
                                        //If not, create it
                                        $pickup_point = new \ColissimoPickupPoint();
                                        $pickup_point->colissimo_id = pSQL($pickup_id);
                                        $pickup_point->company_name = pSQL($pickup_address['name']);
                                        $pickup_point->address1 = pSQL($pickup_address['street']);
                                        $pickup_point->address2 = '';
                                        $pickup_point->address3 = '';
                                        $pickup_point->city = pSQL($pickup_address['city']);
                                        $pickup_point->zipcode = pSQL($pickup_address['postal_code']);
                                        $pickup_point->country = Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), $id_country);
                                        $pickup_point->iso_country = $pickup_address['country'];
                                        $pickup_point->product_code = $product_code;
                                        $pickup_point->network = $network;

                                        try {
                                            $pickup_point->save();
                                        } catch (Exception $e) {}
                                    }

                                    \ColissimoCartPickupPoint::updateCartPickupPoint($cart->id, $pickup_point->id, $phone);
                                }

                                if (($oyst_carrier_type == 'colissimo_poste' || $oyst_carrier_type == 'colissimo_commerces') && Module::isEnabled('socolissimo')) {
                                    if ($oyst_carrier_type == 'colissimo_poste') {
                                        $delivery_mode = 'BPR';
                                    } else {
                                        $delivery_mode = 'A2P';
                                    }

                                    $data = array(
                                        'id_cart' => $cart->id,
                                        'id_customer' => $cart->id_customer,
                                        'delivery_mode' => $delivery_mode,
                                        'prid' => pSQL($pickup_id),
                                        'prname' => ($pickup_address['name'] != '') ? pSQL($pickup_address['name']) : 'none',
                                        'prfirstname' => pSQL($oyst_invoice_address['firstname']),
                                        'pradress4' => pSQL($pickup_address['street']),
                                        'przipcode' => pSQL($pickup_address['postal_code']),
                                        'prtown' => pSQL($pickup_address['city']),
                                        'cecountry' => pSQL($pickup_address['country']),
                                        'cephonenumber' => pSQL($phone),
                                        'ceemail' => pSQL($oyst_invoice_address['email']),
                                        'cecompanyname' => pSQL($oyst_invoice_address['company']),
                                    );

                                    Db::getInstance()->insert(
                                        'socolissimo_delivery_info',
                                        $data
                                    );
                                }
                            }

                            if ($oyst->validateOrder($cart->id, Configuration::get('OYST_OS_PAY_WAITING_TO_CAPTURE'), $total, $oyst->displayName, null, array(), (int)$cart->id_currency, false, $cart->secure_key)) {
                                $notification->complete($oyst->currentOrder);
                                // Set id_cart to 0 to avoid cart deletion on front office
                                $order = new Order($oyst->currentOrder);
                                if (Validate::isLoadedObject($order)) {
                                    $order->id_cart = 0;
                                    $order->update();

                                    if (isset($params['data']['pickup_store']['address'])) {

                                        // Insert data on table mr_selected for pickup mondial relay
                                        if ($oyst_carrier_type == 'mondial_relay' &&
                                            Module::isEnabled('mondialrelay')) {
                                            $id_mr_method = (int)Db::getInstance()->getValue(
                                                "SELECT m.id_mr_method
                                                FROM `"._DB_PREFIX_."mr_method` m
                                                LEFT JOIN `"._DB_PREFIX_."mr_method_shop` ms ON (ms.id_mr_method = m.id_mr_method AND ms.`id_shop` = ".Context::getContext()->shop->id.")
                                                WHERE m.`id_carrier` = ".$carrier->id." AND m.`is_deleted` = 0"
                                            );

                                            $md_data = [];
                                            $md_data[] = [
                                                'id_customer' => $cart->id_customer,
                                                'id_method' => $id_mr_method,
                                                'id_cart' => $cart->id,
                                                'id_order' => $order->id,
                                                'MR_Selected_Num' => pSQL($pickup_id),
                                                'MR_Selected_LgAdr1' => ($pickup_address['name'] != '') ? pSQL($pickup_address['name']) : 'NULL',
                                                'MR_Selected_LgAdr3' => ($pickup_address['street'] != '') ? pSQL($pickup_address['street']) : 'NULL',
                                                'MR_Selected_CP' => ($pickup_address['postal_code'] != '') ? (int)$pickup_address['postal_code'] : 'NULL',
                                                'MR_Selected_Ville' => ($pickup_address['city'] != '') ? pSQL($pickup_address['city']) : 'NULL',
                                                'MR_Selected_Pays' => ($pickup_address['country'] != '') ? pSQL($pickup_address['country']) : 'NULL',
                                            ];

                                            Db::getInstance()->insert('mr_selected', $md_data);
                                        }

                                        if (Module::isEnabled('envoidunet')) {
                                            $envoidunet = Module::getInstanceById(Module::getModuleIdByName('envoidunet'));
                                            $edn_carrier = $envoidunet->getMapping()->getEdnCarrier($carrier->id);
                                            if (!empty($edn_carrier['service']) && $edn_carrier['service'] == 'relay') {
                                                Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."edn_cart_relay` (id_cart, id_relay) 
                                                    VALUES (".$cart->id.", '".pSql($pickup_id)."') 
                                                    ON DUPLICATE KEY UPDATE id_relay='".pSql($pickup_id)."'");
                                            }
                                        }

                                        if (($oyst_carrier_type == 'colissimo_poste' || $oyst_carrier_type == 'colissimo_commerces') &&
                                            (Module::isEnabled('soflexibilite') || Module::isEnabled('soliberte'))) {

                                            //If soflexibilite
                                            if (Module::isEnabled('soflexibilite')) {
                                                $delivery = new \SoFlexibiliteDelivery($cart->id, $cart->id_customer);
                                            } else {
                                                //Else soliberte
                                                $delivery = new \SoLiberteDelivery($cart->id, $cart->id_customer);
                                            }

                                            $delivery->loadDelivery();
                                            $delivery->postcode = $pickup_address['postal_code'];
                                            $delivery->city = $pickup_address['city'];
                                            $delivery->country = $pickup_address['country'];
                                            $delivery->address1 = $pickup_address['street'];
                                            $delivery->saveDelivery();
                                        }
                                    }
                                }

                                if (Configuration::get('OYST_HIDE_ERRORS')) {
                                    $buffer = ob_get_contents();
                                    if (!empty($buffer)) {
                                        $this->logger->warning('Something was print before json : '.$buffer);
                                    }
                                    ob_clean();
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
            $prestashop_status_id = OystStatusService::getInstance()->getPrestashopStatusIdFromOystStatus($params['data']['oystOrder']['status']['code']);
            if (!empty($prestashop_status_id)) {
                $notification = Notification::getNotificationByOystId($params['url']['id']);
                if (Validate::isLoadedObject($notification)) {
                    try {
                        $order = new Order($notification->order_id);
                        if (Validate::isLoadedObject($order)) {
                            if ($order->getCurrentState() != $prestashop_status_id) {
                                // If status oyst_payment_captured => send order email to customer
                                $history = new OrderHistory();
                                $history->id_order = $notification->order_id;
                                $history->changeIdOrderState($prestashop_status_id, $order, true);
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
