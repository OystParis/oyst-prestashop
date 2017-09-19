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

use Db;
use Oyst\Classes\OneClickShipment;
use Customer;
use Cart;
use Oyst\Repository\AddressRepository;
use Address;
use Tools;
use Validate;
use Currency;
use Tax;
use TaxCalculator;
use Carrier;
use Configuration as PSConfiguration;
use Exception;

class ShipmentService extends AbstractOystService
{
    /** @var AddressRepository */
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
     * @param OneClickShipment $shipment
     * @return bool
     */
    public function pushShipment(OneClickShipment $shipment)
    {
        $this->pushShipments(array($shipment));
    }

    /**
     * @param OneClickShipmentService[] $shipments
     *
     * @return bool
     */
    public function pushShipments($shipments)
    {
        $result = $this->requester->call('postShipments', array($shipments));

        if (!isset($result['shipments']) || !count($result['shipments'])) {
            $this->logger->alert('No shipment(s) sent');
        }

        return isset($result['shipments']);
    }

    /**
     * @param $data
     * @return array
     * @throws Exeption
     */
    public function getShipments($data)
    {
        $result = array();

        $customer = $this->getCustomer($data['user']);
            if (!Validate::isLoadedObject($customer)) {
                $result['error'] = 'Customer not found or can\'t be found';
            }

        $addressRepository = new AddressRepository(Db::getInstance());
        $address = $addressRepository->findAddress($data['user']['address']);

        // PS core used this context anywhere.. So we need to fill it properly
            $this->context->cart = $cart = new Cart();
            $this->context->customer = $customer;
        // For debug but when prod pass in context object currency
        $this->context->currency = new Currency(Currency::getIdByIsoCode('EUR'));

        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $address->id;
        $cart->id_address_invoice = $address->id;
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

        foreach($data['items'] as $key => $item) {
            $cart->updateQty($item['quantity'], $item['reference'], null, false, 'up', $address->id);
            $result['items'][$key]['reference'] = $item['reference'];
            $result['items'][$key]['quantity'] = $item['quantity'];
        }

        $carriersAvailables = $cart->simulateCarriersOutput();

        //die(var_dump($carriersAvailables));

        foreach($carriersAvailables as $key => $shipment) {
            $id_carrier = Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1); // Get id carrier

            // Get amount with tax
            $amount = 0;
            $carrier = new Carrier($id_carrier);
            $tax = new Tax();
            $tax->rate = $carrier->getTaxesRate($address);
            $tax_calculator = new TaxCalculator(array($tax));
            $amount += $tax_calculator->addTaxes($shipment['price_tax_exc']);

            $result['shipments'][$key]['amount']['currency'] = 'EUR';
            //$result['shipments'][$key]['amount']['amount'] = (int)round($amount * 100);
            $result['shipments'][$key]['amount']['amount'] = $amount;
        }

        die(var_dump(json_encode($result)));
    }
}
