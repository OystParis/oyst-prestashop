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

use Oyst\Classes\OneClickShipmentCalculation;
use Oyst\Classes\OneClickShipmentCatalogLess;
use Oyst\Classes\OneClickItem;
use Oyst\Classes\OystCarrier;
use Oyst\Classes\OystPrice;
use Oyst\Repository\AddressRepository;
use Db;
use Customer;
use Cart;
use Address;
use Tools;
use Validate;
use Currency;
use Context;
use Tax;
use TaxCalculator;
use Carrier;
use Country;
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
            $customer->passwd = Tools::encrypt(Tools::passwdGen());
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
     * @param $data
     * @return array
     * @throws Exeption
     */
    public function getShipments($data)
    {

        // Set delay carrier in hours
        $delay = array(
            0 => 72,
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
            $this->logger->emergency(
                'Customer not found or can\'t be found ['.json_encode($customer).']'
            );
        }

        $addressRepository = new AddressRepository(Db::getInstance());
        $address = $addressRepository->findAddress($data['user']['address']);
        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)Country::getByIso($data['user']['address']['country']);
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            $address = new Address();
            $address->id_customer = $customer->id;
            $address->firstname = $customer->firstname;
            $address->lastname = $customer->lastname;
            $address->address1 = $data['user']['address']['street'];
            $address->postcode = $data['user']['address']['postcode'];
            $address->city = $data['user']['address']['city'];
            $address->alias = 'OystAddress';
            $address->id_country = $countryId;

            $address->add();
        }

        $this->logger->info(
            sprintf(
                'New notification address [%s]',
                json_encode($address)
            )
        );

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
                'Can\'t create cart ['.json_encode($cart).']'
            );
            return false;
        }

        $oneClickShipmentCalculation = new OneClickShipmentCalculation(array());

        if (isset($data['items'])) {
            foreach ($data['items'] as $key => $item) {
                $reference = explode(';', $item['reference']);
                if (!isset($reference[1])) {
                    $reference[1] = null;
                }
                $cart->updateQty($item['quantity'], (int)$reference[0], (int)$reference[1], false, 'up', $address->id);

                //$oneClickItem = new OneClickItem((string)$item['reference'], (int)$item['quantity']);

                //$oneClickShipmentCalculation->addItem($oneClickItem);
            }
        } else {
            $this->logger->emergency(
                'Items not exist ['.json_encode($data).']'
            );
            return false;
        }

        $carriersAvailables = $cart->simulateCarriersOutput();

        $id_default_carrier = (int)PSConfiguration::get('FC_OYST_SHIPMENT_DEFAULT');

        $type = OystCarrier::HOME_DELIVERY;

        foreach ($carriersAvailables as $key => $shipment) {
            $id_carrier = (int)Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1); // Get id carrier

            $id_reference = Db::getInstance()->getValue(
                'SELECT `id_reference`
                FROM `'._DB_PREFIX_.'carrier`
                WHERE id_carrier = '.(int)$id_carrier
            );

            $type_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_".$id_reference);
            $delay_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_DELAY_".$id_reference);

            if (isset($type_shipment) &&
                $type_shipment != '0'
            ) {
                $type = $type_shipment;

                // Get amount with tax
                $carrier = new Carrier($id_carrier);
                $amount = (float) $shipment['price'];

                $oystPrice = new OystPrice($amount, Context::getContext()->currency->iso_code);

                $oneClickShipment = new OneClickShipmentCatalogLess();
                $oystCarrier = new OystCarrier($id_carrier, $shipment['name'], $type);

                $primary = false;
                if ($carrier->id_reference == $id_default_carrier) {
                    $primary =  true;
                }

                $oneClickShipment->setPrimary($primary);
                $oneClickShipment->setAmount($oystPrice);
                if ($delay_shipment && $delay_shipment != '') {
                    $oneClickShipment->setDelay((int)$delay_shipment * 24);
                } else {
                    $oneClickShipment->setDelay($delay[(int)$carrier->grade]);
                }
                $oneClickShipment->setCarrier($oystCarrier);

                $oneClickShipmentCalculation->addShipment($oneClickShipment);
            }
        }

        // Check exist primary
        $is_primary = false;

        foreach ($carriersAvailables as $key => $shipment) {
            $carrier_desintifier = Cart::desintifier($shipment['id_carrier']);
            $id_carrier = (int)Tools::substr($carrier_desintifier, 0, -1);
            $id_reference = Db::getInstance()->getValue('
                SELECT `id_reference`
                FROM `'._DB_PREFIX_.'carrier`
                WHERE id_carrier = '.(int)$id_carrier);
            if ($id_reference == $id_default_carrier) {
                $is_primary = true;
            }
        }

        // Add first carrier if primary is not exist
        if (!$is_primary) {
            $oneClickShipmentCalculation->setDefaultPrimaryShipmentByType();
        }

        $this->logger->info(
            sprintf(
                'New notification oneClickShipmentCalculation [%s]',
                $oneClickShipmentCalculation->toJson()
            )
        );

        // Delete cart for module relaunch cart
        $cart->delete();

        return $oneClickShipmentCalculation->toJson();
    }
}
