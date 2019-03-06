<?php

namespace Oyst\Services;

use Address;
use Country;
use Db;
use Language;
use Validate;

class AddressService {

    //Mapping for addresses
    private $mapping_fields = array(
        'street1' => 'address1',
        'street2' => 'address2',
    );

    const OYST_FAKE_ADDR_ALIAS = 'Oyst fake address';
    const OYST_CART_ADDR = 'Oyst';

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AddressService();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function formatAddressForPrestashop($address)
    {
        foreach ($this->mapping_fields as $oyst_name => $prestashop_name) {
            if (isset($address[$oyst_name])) {
                $address[$prestashop_name] = $address[$oyst_name];
            }
        }

        if (!empty($address['country']) && $id_country = Country::getByIso($address['country']['code'])) {
            $address['id_country'] = $id_country;
            unset($address['country']);
        }

        $address['alias'] = self::OYST_CART_ADDR;

        return $address;
    }

    public function findExistentAddress($existent_addresses, $address)
    {
        $id_address = 0;
        $oyst_fields = array_keys($address);

        foreach ($existent_addresses as $existant_address) {
            $address_finded = true;
            foreach ($oyst_fields as $oyst_field) {
                if (isset($existant_address[$oyst_field]) && $oyst_field != 'alias') {
                    $address_finded &= ($existant_address[$oyst_field] == $address[$oyst_field]);
                }
            }
            if ($address_finded) {
                $id_address = $existant_address['id_address'];
                break;
            }
        }
        return $id_address;
    }

    public function findOystAddress($customer)
    {
        $addresses = $customer->getAddresses(Language::getIdByIso('fr'));
        foreach ($addresses as $address) {
            if ($address['alias'] == self::OYST_CART_ADDR) {
                return $address;
            }
        }
        return null;
    }

    public function getFakeAddress()
    {
        $id_address = Db::getInstance()->getValue("SELECT id_address FROM "._DB_PREFIX_."address WHERE `alias` = '".self::OYST_FAKE_ADDR_ALIAS."'");
        $address = new Address($id_address);
        if (Validate::isLoadedObject($address)) {
            return $address;
        } else {
            return null;
        }
    }
}
