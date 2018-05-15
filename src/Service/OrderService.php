<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

namespace Oyst\Service;

use Address;
use Order;
use OrderHistory;
use Oyst\Repository\AddressRepository;
use Carrier;
use Cart;
use Combination;
use Configuration as PSConfiguration;
use Country;
use Currency;
use Customer;
use Exception;
use Oyst\Repository\OrderRepository;
use Oyst\Service\Http\CurrentRequest;
use Product;
use ToolsCore;
use Validate;
use Db;
use Tools;
use StockAvailable;
use CartRule;
use Oyst;

/**
 * Class OneClickService
 */
class OrderService extends AbstractOystService
{
    use ToolServiceTrait;

    /** @var AddressRepository */
    private $addressRepository;

    /** @var OrderRepository */
    private $orderRepository;

    /**
     * @param Customer $customer
     * @param array $oystAddress
     * @return Address
     */
    private function getInvoiceAddress(Customer $customer, $oystUser)
    {
        $oystAddress = $oystUser['address'];
        $address = $this->addressRepository->findAddress($oystAddress, $customer);
        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)Country::getByIso('fr');
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $oystAddress['first_name']);
            if (isset(Address::$definition['fields']['firstname']['size'])) {
                $firstname = Tools::substr($firstname, 0, Address::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $oystAddress['last_name']);
            if (isset(Address::$definition['fields']['lastname']['size'])) {
                $lastname = Tools::substr($lastname, 0, Address::$definition['fields']['lastname']['size']);
            }

            $address->id_customer = $customer->id;
            $address->firstname = $firstname;
            $address->lastname = $lastname;
            $address->address1 = $oystAddress['street'];
            $address->postcode = $oystAddress['postcode'];
            $address->city = $oystAddress['city'];
            $address->alias = 'OystAddress';
            $address->id_country = $countryId;
            $address->phone = $oystUser['phone'];
            $address->phone_mobile = $oystUser['phone'];

            if (isset($oystAddress['company_name'])) {
                $address->company = $oystAddress['company_name'];
            }

            if (isset($oystAddress['complementary'])) {
                $address->address2 = $oystAddress['complementary'];
            }

            $address->add();
        }

        return $address;
    }

    /**
     * @param $shipmentInfo
     * @return Address
     */
    private function getPickupStoreAddress(Customer $customer, $shipmentInfo, $phone = '0600000000')
    {
        $pickupAddress = $shipmentInfo['pickup_store']['address'];
        $pickupId = $shipmentInfo['pickup_store']['id'];
        $carrierInfo = $shipmentInfo['carrier'];

        $alias = Tools::substr('Pickup_'.str_replace(' ', '_', $pickupAddress['name']), 0, 32);
        $addressToFind = array(
            'name' => $alias,
            'street' => $pickupAddress['street'],
            'postcode' => $pickupAddress['postal_code'],
            'city' => $pickupAddress['city'],
        );

        $address = $this->addressRepository->findAddress($addressToFind, $customer);
        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)Country::getByIso('fr');
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            if ($pickupAddress['name'] != '') {
                $pickup_name = $pickupAddress['name'];
            } else {
                $pickup_name = 'none';
            }

            $address = new Address();
            $address->id_customer = $customer->id;
            $address->firstname = $customer->firstname;
            $address->lastname = $customer->lastname;
            $address->address1 = $pickup_name.' - '.($pickupAddress['street'] != '' ? $pickupAddress['street'] : 'none');
            $address->postcode = ($pickupAddress['postal_code'] != '')? $pickupAddress['postal_code'] : 'none';
            $address->city = ($pickupAddress['city'] != '')? $pickupAddress['city'] : 'none';
            $address->alias = $alias;
            $address->id_country = $countryId;
            $address->other = 'Pickup Info #'.$pickupId.' type '.$carrierInfo['type'];
            $address->phone = $phone;
            $address->phone_mobile = $phone;

            $address->add();
        }

        return $address;
    }

    /**
     * @param Customer $customer
     * @param Address $invoiceAddress
     * @param Address $deliveryAddress
     * @param $products
     * @param $oystOrderInfo
     * @return bool
     */
    public function createNewOrder(Customer $customer, Address $invoiceAddress, Address $deliveryAddress, $products, $oystOrderInfo)
    {
        $request = new CurrentRequest();
        $data = $request->getJson();
        $forceResendOrder = (isset($data['notification']) && $data['notification']);
        // PS core used this context anywhere.. So we need to fill it properly
        if ($oystOrderInfo['context'] && isset($oystOrderInfo['context']['id_cart']) && !$forceResendOrder) {
            $id_cart = $oystOrderInfo['context']['id_cart'];
            $cart = new Cart($id_cart);
            $products_cart = $cart->getProducts();
            foreach ($products_cart as $p) {
                $customizations = $cart->getProductCustomization($p['id_product']);
                foreach ($customizations as $customization) {
                    $cart->deleteProduct((int)$p['id_product'], (int)$p['id_product_attribute'], (int)$customization['id_customization']);
                }
                $cart->deleteProduct((int)$p['id_product'], (int)$p['id_product_attribute']);
            }
            $this->context->cart = $cart;
        } else {
            $this->context->cart = $cart = new Cart();
        }

        if ($oystOrderInfo['context'] && isset($oystOrderInfo['context']['user_agent'])) {
            $user_agent = $oystOrderInfo['context']['user_agent'];
        } else {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        }

        $this->context->customer = $customer;
        $this->context->currency = new Currency(Currency::getIdByIsoCode($oystOrderInfo['order_amount']['currency']));

        if (!Validate::isLoadedObject($this->context->currency)) {
            $this->logger->emergency(
                'Currency not found: '.$oystOrderInfo['order_amount']['currency']
            );
            return false;
        }

        //If country's context is disabled, this will be throw an exception in the ValidateOrder method so let's check that
        if (isset($this->context->country) && !$this->context->country->active) {
            $this->context->country = new Country($deliveryAddress->id_country);
            if (!Validate::isLoadedObject($this->context->country)) {
                $this->context->country = new Country($invoiceAddress->id_country);
                $this->logger->emergency(
                    'Country not found: '.$deliveryAddress->id_country.' or '.$invoiceAddress->id_country
                );
                return false;
            }
        }

        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $deliveryAddress->id;
        $cart->id_address_invoice = $invoiceAddress->id;
        $cart->id_lang = $this->context->language->id;
        $cart->secure_key = $customer->secure_key;
        $cart->id_shop = PSConfiguration::get('PS_SHOP_DEFAULT');
        $cart->id_currency = $this->context->currency->id;

        if (!$cart->save()) {
            $this->logger->emergency(
                'Can\'t create cart ['.$this->serializer->serialize($cart).']'
            );
            return false;
        }


        foreach ($products as $productInfo) {
            $custom_qty = 0;
            $product = new Product((int)$productInfo['productId']);

            if ($product->advanced_stock_management == 0  && PSConfiguration::get('FC_OYST_SHOULD_AS_STOCK')) {
                StockAvailable::updateQuantity($productInfo['productId'], $productInfo['combinationId'], $productInfo['quantity']);
            }

            if (!empty($productInfo['customizations'])) {
                foreach ($productInfo['customizations'] as $customization) {
                    foreach ($customization['data'] as $datum) {
                        if ($datum['type'] == 0) {
                            $oyst_upload_dir = _PS_UPLOAD_DIR_.'oyst/';
                            if (file_exists($oyst_upload_dir.$datum['value'])) {
                                rename($oyst_upload_dir.$datum['value'], _PS_UPLOAD_DIR_.'/'.$datum['value']);
                                rename($oyst_upload_dir.$datum['value'].'_small', _PS_UPLOAD_DIR_.'/'.$datum['value'].'_small');
                            }
                            $cart->addPictureToProduct($productInfo['productId'], $datum['index'], $datum['type'], $datum['value']);
                        } elseif ($datum['type'] == 1) {
                            $cart->addTextFieldToProduct($productInfo['productId'], $datum['index'], $datum['type'], $datum['value']);
                        }
                    }
                    $custom_qty += $customization['quantity'];
                    //Get inserted id_customization
                    $id_customization = Db::getInstance()->getValue("SELECT `id_customization`
                        FROM `"._DB_PREFIX_."customization`
                        WHERE `id_product` = ".(int)$productInfo['productId']."
                        AND `id_product_attribute` = ".(int)$productInfo['combinationId']."
                        AND `id_cart` = ".(int)$cart->id."
                        ORDER BY `id_customization` DESC");

                    if (!$cart->updateQty($customization['quantity'], $productInfo['productId'], $productInfo['combinationId'], $id_customization)) {
                        $this->logger->emergency(
                            sprintf(
                                "Can't add product to cart, please check the quantity.
                                Product #%d. Combination #%d. Quantity %d, Customization %d",
                                $productInfo['productId'],
                                $productInfo['combinationId'],
                                $productInfo['quantity'],
                                $id_customization
                            )
                        );
                    }
                }
            }
            if ($custom_qty < $productInfo['quantity']) {
                if (!$cart->updateQty($productInfo['quantity']-$custom_qty, $productInfo['productId'], $productInfo['combinationId'])) {
                    $this->logger->emergency(
                        sprintf(
                            "Can't add product to cart, please check the quantity.
                            Product #%d. Combination #%d. Quantity %d",
                            $productInfo['productId'],
                            $productInfo['combinationId'],
                            $productInfo['quantity']
                        )
                    );
                }
            }
        }
        // Manage cart rule
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $id_reference = Db::getInstance()->getValue('
                        SELECT `id_reference`
                        FROM `'._DB_PREFIX_.'carrier`
                        WHERE id_carrier = '.(int)$oystOrderInfo['shipment']['carrier']['id']);

        // Require to get the right price during the validateOrder
        $cart->oystShipment = $oystOrderInfo['shipment'];
        $cart->id_carrier = Carrier::getCarrierByReference($id_reference)->id;
        $delivery_option = $cart->getDeliveryOption();
        $delivery_option[$cart->id_address_delivery] = $cart->id_carrier .",";
        $cart->setDeliveryOption($delivery_option);
        $cart->update();

        // Yes not used but it will flush the delivery cache, instead, default carrier will be used
        $cart->getOrderTotal();

        if (PSConfiguration::get('FC_OYST_STATE_PAYMENT_ONECLICK')) {
            $order_state = PSConfiguration::get('FC_OYST_STATE_PAYMENT_ONECLICK');
        } else {
            $order_state = PSConfiguration::get('PS_OS_PAYMENT');
        }

        // Get cookie Oyst
        $cookie_oyst = Tools::file_get_contents('https://api.oyst.com/session');
        $cookie_oyst = json_decode($cookie_oyst);

        // Get cURL resource
        $ch = curl_init();
        $oyst = new Oyst();

        // Set url
        $env = $oyst->getOneClickEnvironment();

        switch ($env) {
            case \Oyst\Service\Configuration::API_ENV_PROD:
                $url = 'https://api.oyst.com/events/oneclick';
                break;
            case \Oyst\Service\Configuration::API_ENV_SANDBOX:
                $url = 'https://api.sandbox.oyst.eu/events/oneclick';
                break;
            case \Oyst\Service\Configuration::API_ENV_CUSTOM:
                $url = $oyst->getCustomOneClickApiUrl().'/events/oneclick';
                break;
            default:
                $url = 'https://api.oyst.com/events/oneclick';
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Set headers
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            ["Content-Type: application/json; charset=utf-8"]
        );


        // Create body
        $json_array = array(
            "referrer" => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "",
            "tag" => "merchantconfirmationpage:display",
            "oyst_cookie" => isset($cookie_oyst->esid)? $cookie_oyst->esid : "",
            "user_agent" => $user_agent,
            "cart_amount" => $oystOrderInfo['order_amount']['value'],
            "payment" => "Oyst OneClick",
            "timestamp" => time()
        );

        $body = json_encode($json_array);

        // Set body
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        // Send the request & save response to $resp
        $resp = curl_exec($ch);

        // Close request to clear up some resources
        curl_close($ch);

        $state = $this->oyst->validateOrder(
            $cart->id,
            $order_state,
            $oystOrderInfo['order_amount']['value'] / 100,
            'Oyst OneClick',
            null,
            array(),
            null,
            true,
            $cart->secure_key
        );

        if ($state) {
            $order = new Order(Order::getOrderByCartId($cart->id));
            $this->orderRepository->linkOrderToGUID($order, $oystOrderInfo['id']);

            Db::getInstance()->update(
                'oyst_payment_notification',
                array(
                    'id_order'   => (int)$order->id,
                    'id_cart'    => (int)$cart->id,
                    'event_data' => pSQL(Tools::jsonEncode($oystOrderInfo)),
                    'date_event' => pSQL(Tools::substr(str_replace('T', ' ', $oystOrderInfo['created_at']), 0, 19)),
                    'status'     => 'finished',
                    'date_upd'   => date('Y-m-d H:i:s'),
                ),
                'payment_id = "'.pSQL($oystOrderInfo['id']).'" AND `status` = "start"'
            );
        }

        return $state;
    }

    /**
     * @param $oystOrderId
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function getOrderInfo($oystOrderId)
    {
        $oystOrderInfo = $this->requester->call('getOrder', array($oystOrderId));

        return $oystOrderInfo;
    }

    /**
     * @param $orderId
     *
     * @return array
     *
     * @throws Exception
     */
    public function requestCreateNewOrder($orderId)
    {
        $data = array(
            'state' => false,
        );

        $oystOrderInfo = $this->getOrderInfo($orderId);
        if ($oystOrderInfo) {
            $products = array();
            foreach ($oystOrderInfo['order']['items'] as $productInfo) {
                $reference = explode(';', $productInfo['product_reference']);
                $product = new Product($reference[0]);

                if (!Validate::isLoadedObject($product)) {
                    $data['error'] = 'Product has not been found';
                }

                $combination = new Combination();
                // Array will exist but reference could be null
                if (!empty($reference[1])) {
                    $combination = new Combination($reference[1]);
                    if (!Validate::isLoadedObject($combination)) {
                        $data['error'] = 'Combination has not been found';
                    }
                }

                $products[] = array(
                    'productId' => $product->id,
                    'combinationId' => $combination->id,
                    'quantity' => $productInfo['quantity'],
                    'customizations' => $productInfo['product']['customizations'],
                );
            }

            if ($oystOrderInfo['order']['is_cart_checkout']) {
                // Add free item
                if (($free_items = $oystOrderInfo['order']['merchant_free_items']) &&
                    isset($oystOrderInfo['order']['merchant_free_items'])) {
                    foreach ($free_items as $free_item) {
                        $ref_item = explode(';', $free_item['reference']);
                        $product = new Product($ref_item[0]);

                        if (!Validate::isLoadedObject($product)) {
                            $data['error'] = 'Product has not been found';
                        }

                        $combination = new Combination();
                        // Array will exist but reference could be null
                        if (!empty($ref_item[1])) {
                            $combination = new Combination($ref_item[1]);
                            if (!Validate::isLoadedObject($combination)) {
                                $data['error'] = 'Combination has not been found';
                            }
                        }

                        $products[] = array(
                            'productId' => $product->id,
                            'combinationId' => $combination->id,
                            'quantity' => $free_item['quantity'],
                        );
                    }
                }
            }

            if ($oystOrderInfo['order']['context'] && isset($oystOrderInfo['order']['context']['id_user'])) {
                $customer = new Customer((int)$oystOrderInfo['order']['context']['id_user']);
            } else {
                $customer = $this->getCustomer($oystOrderInfo['order']['user']);
            }
            if (!Validate::isLoadedObject($customer)) {
                $data['error'] = 'Customer not found or can\'t be found';
            }

            $invoiceAddress = $this->getInvoiceAddress($customer, $oystOrderInfo['order']['user']);
            if (!Validate::isLoadedObject($invoiceAddress)) {
                $data['error'] = 'Address not found or can\'t be created';
            }

            //Fix for retroactivity for missing phone bug or phone
            if ($invoiceAddress->phone_mobile == '' || $invoiceAddress->phone == '') {
                $invoiceAddress->phone = $oystOrderInfo['order']['user']['phone'];
                $invoiceAddress->phone_mobile = $oystOrderInfo['order']['user']['phone'];
                $invoiceAddress->update();
            }

            if (!isset($oystOrderInfo['order']['shipment']['pickup_store'])) {
                $deliveryAddress = $invoiceAddress;
            } else {
                $deliveryAddress = $this->getPickupStoreAddress($customer, $oystOrderInfo['order']['shipment'], $oystOrderInfo['order']['user']['phone']);
            }

            if (!isset($data['error'])) {
                $state = $this->createNewOrder($customer, $invoiceAddress, $deliveryAddress, $products, $oystOrderInfo['order']);
                $data['state'] = $state;
            } else {
                if ($oystOrderInfo['order']['is_cart_checkout']) {
                    $insert   = array(
                        'id_order'   => 0,
                        'id_cart'    => $oystOrderInfo['order']['context']['id_cart'],
                        'payment_id' => '',
                        'event_code' => 'error.found.cart',
                        'event_data' => pSQL($data['error']),
                        'date_event' => date('Y-m-d H:i:s'),
                        'date_add'   => date('Y-m-d H:i:s'),
                    );
                    Db::getInstance()->insert('oyst_payment_notification', $insert);
                }
            }
        } else {
            $data['error'] = $this->requester->getApiClient()->getLastError();
            $data['httpCode'] = $this->requester->getApiClient()->getLastHttpCode();
        }

        return $data;
    }

    /**
     * @param $orderId
     * @param $status
     *
     * @return bool
     */
    public function updateOrderStatus($orderId, $status)
    {
        $this->requester->call('updateStatus', array((string) $orderId, $status));

        $succeed = false;
        if ($this->requester->getApiClient()->getLastHttpCode() != 200) {
            $this->logger->warning(sprintf('Oyst order %s has not been updated to %s', $orderId, $status));
        } else {
            $succeed = true;
            $this->logger->info(sprintf('Oyst order %s has been updated to %s', $orderId, $status));
        }

        return $succeed;
    }

    public function updateOrderStatusPresta($order_guid, $status, $oystData)
    {
        $order_id = $this->getOrderRepository()->getOrderId($order_guid);
        $order = new Order($order_id);

        if (Validate::isLoadedObject($order)) {
            $id_order_state = PSConfiguration::get($status);

            if ($id_order_state > 0) {
                if ($order->current_state == $id_order_state) {
                    header("HTTP/1.1 400 Bad Request");
                    header('Content-Type: application/json');
                    die(json_encode(array(
                        'code' => 'status-already-set',
                        'message' => 'This status is the current status',
                    )));
                }
                $insert = array(
                    'id_order' => (int)$order->id,
                    'id_cart' => (int)$order->id_cart,
                    'payment_id' => pSQL($order_guid),
                    'event_code' => pSQL($oystData['event']),
                    'event_data' => pSQL(Tools::jsonEncode($oystData['data'])),
                    'status' => 'start',
                    'date_event' => date('Y-m-d H:i:s'),
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                );
                Db::getInstance()->insert('oyst_payment_notification', $insert);
                $id_notification = Db::getInstance()->Insert_ID();

                // Create new OrderHistory
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->id_employee = 0;
                $history->id_order_state = (int)$id_order_state;
                $history->changeIdOrderState((int)$id_order_state, $order->id);
                $history->add();

                $update = array(
                    'status' => 'finished',
                    'date_upd' => date('Y-m-d H:i:s'),
                );
                Db::getInstance()->update('oyst_payment_notification', $update, 'id_oyst_payment_notification = '.$id_notification);

                return json_encode(array('state' => true));
            } else {
                header("HTTP/1.1 400 Bad Request");
                header('Content-Type: application/json');
                die(json_encode(array(
                    'code' => 'fraud-status-not-exists',
                    'message' => 'Status '.$status.' not found in Prestashop',
                )));
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            header('Content-Type: application/json');
            die(json_encode(array(
                'code' => 'unknown-order',
                'message' => 'Order not found',
            )));
        }
    }

    /**
     * @param $orderId
     * @param OystPrice $price
     *
     * @return bool
     */
    public function refunds($guid, $price)
    {
        $this->requester->call('refunds', array($guid, $price));

        $succeed = false;
        if ($this->requester->getApiClient()->getLastHttpCode() != 200) {
            $this->logger->warning(sprintf('Oyst order %s has not been updated', $guid));
        } else {
            $succeed = true;
            $this->logger->info(sprintf('Oyst order %s has been updated', $guid));
        }

        return $succeed;
    }

    /**
     * @return AddressRepository
     */
    public function getAddressRepository()
    {
        return $this->addressRepository;
    }

    /**
     * @param \Oyst\Repository\AddressRepository $addressRepository
     *
     * @return $this
     */
    public function setAddressRepository($addressRepository)
    {
        $this->addressRepository = $addressRepository;

        return $this;
    }

    /**
     * @return OrderRepository
     */
    public function getOrderRepository()
    {
        return $this->orderRepository;
    }

    /**
     * @param OrderRepository $orderRepository
     *
     * @return $this
     */
    public function setOrderRepository($orderRepository)
    {
        $this->orderRepository = $orderRepository;

        return $this;
    }
}
