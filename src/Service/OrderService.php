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
use CountryCore;
use Currency;
use Customer;
use Exception;
use Oyst\Repository\OrderRepository;
use Product;
use ToolsCore;
use Validate;
use Db;
use Tools;

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
            $customer = new Customer();
            $customer->email = $user['email'];
            $customer->firstname = $user['address']['first_name'];
            $customer->lastname = $user['address']['last_name'];
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
    private function getInvoiceAddress(Customer $customer, $oystAddress)
    {
        $address = $this->addressRepository->findAddress($oystAddress);
        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)CountryCore::getByIso('fr');
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            $address->id_customer = $customer->id;
            $address->firstname = $customer->firstname;
            $address->lastname = $customer->lastname;
            $address->address1 = $oystAddress['street'];
            $address->postcode = $oystAddress['postcode'];
            $address->city = $oystAddress['city'];
            $address->alias = 'OystAddress';
            $address->id_country = $countryId;

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
    private function getPickupStoreAddress($shipmentInfo)
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

        $address = $this->addressRepository->findAddress($addressToFind);
        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)CountryCore::getByIso('fr');
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            $address = new Address();
            $address->firstname = ($pickupAddress['name'] != '')? $pickupAddress['name'] : 'none';
            $address->lastname = "";
            $address->address1 = ($pickupAddress['street'] != '')? $pickupAddress['street'] : 'none';
            $address->postcode = ($pickupAddress['postal_code'] != '')? $pickupAddress['postal_code'] : 'none';
            $address->city = ($pickupAddress['city'] != '')? $pickupAddress['city'] : 'none';
            $address->alias = $alias;
            $address->id_country = $countryId;
            $address->other = 'Pickup Info #'.$pickupId.' type '.$carrierInfo['type'];

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
        // PS core used this context anywhere.. So we need to fill it properly
        $this->context->cart = $cart = new Cart();
        $this->context->customer = $customer;
        $this->context->currency = new Currency(Currency::getIdByIsoCode($oystOrderInfo['order_amount']['currency']));

        if (!Validate::isLoadedObject($this->context->currency)) {
            $this->logger->emergency(
                'Currency not found: '.$oystOrderInfo['order_amount']['currency']
            );
            return false;
        }

        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $deliveryAddress->id;
        $cart->id_address_invoice = $invoiceAddress->id;
        $cart->id_lang = $customer->id_lang;
        $cart->secure_key = $customer->secure_key;
        $cart->id_shop = PSConfiguration::get('PS_SHOP_DEFAULT');
        $cart->id_currency = $this->context->currency->id;

        if (!$cart->add()) {
            $this->logger->emergency(
                'Can\'t create cart ['.$this->serializer->serialize($cart).']'
            );
            return false;
        }

        foreach ($products as $productInfo) {
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
            foreach ($oystOrderInfo['items'] as $productInfo) {
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

            if ($oystOrderInfo['context'] && isset($oystOrderInfo['context']['id_user'])) {
                $customer = new Customer((int)$oystOrderInfo['context']['id_user']);
            } else {
                $customer = $this->getCustomer($oystOrderInfo['user']);
            }
            if (!Validate::isLoadedObject($customer)) {
                $data['error'] = 'Customer not found or can\'t be found';
            }

            $invoiceAddress = $this->getInvoiceAddress($customer, $oystOrderInfo['user']['address']);
            if (!Validate::isLoadedObject($invoiceAddress)) {
                $data['error'] = 'Address not found or can\'t be created';
            }

            if ($invoiceAddress->phone_mobile == '') {
                $invoiceAddress->phone_mobile = $oystOrderInfo['user']['phone'];
                $invoiceAddress->update();
            }

            if (!isset($oystOrderInfo['shipment']['pickup_store'])) {
                $deliveryAddress = $invoiceAddress;
            } else {
                $deliveryAddress = $this->getPickupStoreAddress($oystOrderInfo['shipment']);
            }

            if (!isset($data['error'])) {
                $state = $this->createNewOrder($customer, $invoiceAddress, $deliveryAddress, $products, $oystOrderInfo);
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
