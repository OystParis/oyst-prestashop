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
    private function getAddress(Customer $customer, $oystAddress)
    {
        $address = $this->addressRepository->findAddressUserAddress($oystAddress);
        if (!Validate::isLoadedObject($address)) {
            // TODO: For now, France only, should be changed for worldwide or European
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
     * @param Customer $customer
     * @param Address $address
     * @param $products
     * @param $oystOrderInfo
     * @return bool
     */
    public function createNewOrder(Customer $customer, Address $address, $products, $oystOrderInfo)
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
        $cart->id_address_delivery = $cart->id_address_invoice = $address->id;
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

        // Require to get the right price during the validateOrder
        $cart->oystShipment = $oystOrderInfo['shipment'];
        $cart->id_carrier = Carrier::getCarrierByReference(PSConfiguration::get(Configuration::ONE_CLICK_CARRIER))->id;
        $delivery_option = $cart->getDeliveryOption();
        $delivery_option[$cart->id_address_delivery] = $cart->id_carrier .",";
        $cart->setDeliveryOption($delivery_option);
        $cart->update();

        // Yes not used but it will flush the delivery cache, instead, default carrier will be used
        $cart->getOrderTotal();

        $state = $this->oyst->validateOrder(
            $cart->id,
            PSConfiguration::get('PS_OS_PAYMENT'),
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
                $product = new Product($productInfo['product_reference']);

                if (!Validate::isLoadedObject($product)) {
                    $data['error'] = 'Product has not been found';
                }

                $combination = new Combination();
                // Array will exist but reference could be null
                if (is_array($productInfo['product']['variations']) && null !== $productInfo['product']['variations']['reference']) {
                    $combination = new Combination($productInfo['product']['variations']['reference']);
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

            $customer = $this->getCustomer($oystOrderInfo['user']);
            if (!Validate::isLoadedObject($customer)) {
                $data['error'] = 'Customer not found or can\'t be found';
            }

            $address = $this->getAddress($customer, $oystOrderInfo['user']['address']);
            if (!Validate::isLoadedObject($address)) {
                $data['error'] = 'Address not found or can\'t be created';
            }

            if (!isset($data['error'])) {
                $state = $this->createNewOrder($customer, $address, $products, $oystOrderInfo);
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
