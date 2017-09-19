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
use Context;
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

        // Set delay carrier in hours
        $delay = array(
            0 => 240,
            1 => 216,
            2 => 192,
            3 => 168,
            4 => 144,
            5 => 120,
            6 => 96,
            7 => 72,
            8 => 48,
            9 => 24
        );

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

        $result['order_amount'] = array(
            "currency" => Context::getContext()->currency->iso_code,
            "value" => (int)round($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) * 100)
        );

        $carriersAvailables = $cart->simulateCarriersOutput();

        $id_default_carrier = (int)PSConfiguration::get('FC_OYST_SHIPMENT_DEFAULT');
        $home_delivery = explode(',', PSConfiguration::get('FC_OYST_SHIPMENT_HOME_DELIVERY'));
        $mondial_realy = explode(',', PSConfiguration::get('FC_OYST_SHIPMENT_MONDIAL_RELAY'));
        $pick_up = explode(',', PSConfiguration::get('FC_OYST_SHIPMENT_PICK_UP'));

        $type = "home_delivery";

        foreach($carriersAvailables as $key => $shipment) {
            $id_carrier = (int)Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1); // Get id carrier

            if ($home_delivery != null) {
                if (in_array($id_carrier, $home_delivery))
                    $type = "home_delivery";
            }

            if ($mondial_realy != null) {
                if (in_array($id_carrier, $mondial_realy))
                    $type = "mondial_realy";
            }

            if ($pick_up != null) {
                if (in_array($id_carrier, $pick_up))
                    $type = "pick_up";
            }

            // Get amount with tax
            $amount = 0;
            $carrier = new Carrier($id_carrier);
            $tax = new Tax();
            $tax->rate = $carrier->getTaxesRate($address);
            $tax_calculator = new TaxCalculator(array($tax));
            $amount += $tax_calculator->addTaxes($shipment['price_tax_exc']);

            $result['shipments'][$key]['amount']['currency'] = Context::getContext()->currency->iso_code;
            $result['shipments'][$key]['amount']['amount'] = (int)round($amount * 100);
            $result['shipments'][$key]['delay'] = $delay[(int)$carrier->grade];
            $result['shipments'][$key]['primary'] = ($carrier->id_reference == $id_default_carrier)? true : false;
            $result['shipments'][$key]['carrier']['id'] = $id_carrier;
            $result['shipments'][$key]['carrier']['name'] = $shipment['name'];
            $result['shipments'][$key]['carrier']['type'] = $type;
        }

        return $result;
    }
}
