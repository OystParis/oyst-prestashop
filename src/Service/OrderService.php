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
use Product;
use ToolsCore;
use Validate;
use Db;
use Tools;
use StockAvailable;
use CartRule;

/**
 * Class OneClickService
 */
class OrderService extends AbstractOystService
{
    /** @var AddressRepository */
    private $addressRepository;

    /** @var OrderRepository */
    private $orderRepository;

    /**
     * @param $user
     * @return Customer
     */
    private function getCustomer($user)
    {
        $customerInfo = Customer::getCustomersByEmail($user['email']);
        if (count($customerInfo)) {
            $customer = new Customer($customerInfo[0]['id_customer']);
        } else {
            $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['first_name']);
            if (isset(Customer::$definition['fields']['firstname']['size'])) {
                $firstname = Tools::substr($firstname, 0, Customer::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['last_name']);
            if (isset(Customer::$definition['fields']['lastname']['size'])) {
                $lastname = Tools::substr($lastname, 0, Customer::$definition['fields']['lastname']['size']);
            }

            $customer = new Customer();
            $customer->email = $user['email'];
            $customer->firstname = $firstname;
            $customer->lastname = $lastname;
            $customer->id_lang = PSConfiguration::get('PS_LANG_DEFAULT');
            $customer->passwd = ToolsCore::encrypt(ToolsCore::passwdGen());
            $customer->add();
        }

        return $customer;
    }

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
    public function createNewOrder(Customer $customer, Address $invoiceAddress, Address $deliveryAddress, $products, $oystOrderInfo, $event)
    {
        // PS core used this context anywhere.. So we need to fill it properly
        if ($oystOrderInfo['context'] && isset($oystOrderInfo['context']['id_cart'])) {
            $id_cart = $oystOrderInfo['context']['id_cart'];
            $cart = new Cart($id_cart);
            $products_cart = $cart->getProducts();
            foreach ($products_cart as $p) {
                $cart->deleteProduct((int)$p['id_product'], (int)$p['id_product_attribute']);
            }
            $this->context->cart = $cart;
        } else {
            $this->context->cart = $cart = new Cart();
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
            $product = new Product((int)$productInfo['productId']);

            if ($product->advanced_stock_management == 0  && PSConfiguration::get('FC_OYST_SHOULD_AS_STOCK')) {
                StockAvailable::updateQuantity($productInfo['productId'], $productInfo['combinationId'], $productInfo['quantity']);
            }

            if (!$cart->updateQty($productInfo['quantity'], $productInfo['productId'], $productInfo['combinationId'])) {
                $this->logger->emergency(
                    sprintf(
                        "Can't add product to cart, please check the quantity.
                        Product #%d. Combination #%d. Quantity %d",
                        $productInfo['productId'],
                        $productInfo['combinationId'],
                        $productInfo['quantity']
                    )
                );
                return false;
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
    public function requestCreateNewOrder($orderId, $event)
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
                if (isset($reference[1])) {
                    $combination = new Combination($reference[1]);
                    if (!Validate::isLoadedObject($combination)) {
                        $data['error'] = 'Combination has not been found';
                    }
                }

                $products[] = array(
                    'productId' => $product->id,
                    'combinationId' => $combination->id,
                    'quantity' => $productInfo['quantity'],
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
                        if (isset($reference[1])) {
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
                $state = $this->createNewOrder($customer, $invoiceAddress, $deliveryAddress, $products, $oystOrderInfo['order'], $event);
                $data['state'] = $state;
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
