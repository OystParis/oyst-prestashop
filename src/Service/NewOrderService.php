<?php

namespace Oyst\Service;

use Address;
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
use Oyst\Api\OystOrderApi;
use Product;
use ToolsCore;
use Validate;

/**
 * Class Oyst\Service\OneClickService
 */
class NewOrderService extends AbstractOystService
{
    /** @var  OystOrderApi */
    private $orderApi;

    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  AddressRepository */
    private $addressRepository;

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
     * @param Product $product
     * @param Combination $combination
     * @param $oystOrderInfo
     * @return bool
     */
    public function createNewOrder(Customer $customer, Address $address, Product $product, Combination $combination, $oystOrderInfo)
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

        $carrierReference = PSConfiguration::get('OYST_ONE_CLICK_CARRIER');
        $carrier = Carrier::getCarrierByReference($carrierReference);
        if (!Validate::isLoadedObject($carrier)) {
            $this->logger->emergency(
                'Carrier reference not found: #'.$carrierReference
            );
            return false;
        }

        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $cart->id_address_invoice = $address->id;
        $cart->id_lang = $customer->id_lang;
        $cart->secure_key = $customer->secure_key;
        $cart->id_shop = PSConfiguration::get('PS_SHOP_DEFAULT');
        $cart->id_currency = $this->context->currency->id;
        $cart->id_carrier = $carrier->id;

        if (!$cart->add()) {
            $this->logger->emergency(
                'Can\'t create cart ['.$this->serializer->serialize($cart).']'
            );
            return false;
        } elseif (!$cart->updateQty($oystOrderInfo['quantity'], $product->id, $combination->id)) {
            $this->logger->emergency(
                sprintf(
                    "Can't add product to cart, please check the quantity.
                        Product #%d. Combination #%d",
                    $product->id,
                    $combination->id
                )
            );
            return false;
        }

        $cart->update();

        $key = Cart::desintifier($carrier->id);
        $cart->setDeliveryOption(array($address->id => $key));
        $cart->getDeliveryOptionList(null, true);
        $cart->getDeliveryOption(null, false, false);

        $state = $this->oyst->validateOrder(
            $cart->id,
            PSConfiguration::get('PS_OS_PAYMENT'),
            $oystOrderInfo['order_amount']['value'] / 100,
            'Oyst OneClick',
            null,
            [],
            null,
            true,
            $cart->secure_key
        );

        return $state;
    }

    /**
     * @param $orderId
     * @return array
     * @throws Exception
     */
    public function requestCreateNewOrder($orderId)
    {
        if (null == $this->orderApi) {
            throw new Exception('Did you forget to inject the order api component ?');
        }

        $data = array(
            'state' => false,
        );

        $oystOrderInfo = $this->requestApi($this->orderApi, 'getOrder', $orderId);
        if ($oystOrderInfo) {
            $productReferences = explode('-', $oystOrderInfo['product_reference']);
            $product = new Product($productReferences[0]);

            if (!Validate::isLoadedObject($product)) {
                $data['error'] = 'Product has not been found';
            }

            $combination = new Combination();
            if (isset($productReferences[1])) {
                $combination = new Combination($productReferences[1]);
                if (!Validate::isLoadedObject($combination)) {
                    $data['error'] = 'Combination has not been found';
                }
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
                $state = $this->createNewOrder($customer, $address, $product, $combination, $oystOrderInfo);
                $data['state'] = $state;
            }
        } else {
            $data['error'] = $this->orderApi->getLastError();
            $data['httpCode'] = $this->orderApi->getLastHttpCode();
        }

        return $data;
    }

    /**
     * @param \Oyst\Repository\OrderRepository $orderRepository
     * @return $this
     */
    public function setOrderRepository($orderRepository)
    {
        $this->orderRepository = $orderRepository;

        return $this;
    }

    /**
     * @param OystOrderApi $orderApi
     * @return $this
     */
    public function setOrderApi(OystOrderApi $orderApi)
    {
        $this->orderApi = $orderApi;

        return $this;
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
     * @return $this
     */
    public function setAddressRepository($addressRepository)
    {
        $this->addressRepository = $addressRepository;

        return $this;
    }
}
