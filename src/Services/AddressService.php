<?php

namespace Oyst\Services;

use Country;

class AddressService {

    //Mapping for addresses
    private $mapping_fields = array(
        'street1' => 'address1',
        'street2' => 'address2',
    );

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
        }

        $address['alias'] = 'Oyst';

        return $address;
    }

    public function findExistentAddress($existent_addresses, $address)
    {
        $fields_to_find = array(
            'firstname',
            'lastname',
            'address1',
            'postcode',
            'city',
        );

        $id_address = 0;

        //If fields required for searching are all present in data
        if (count(array_diff($fields_to_find, array_keys($address))) == 0) {
            foreach ($existent_addresses as $existant_address) {
                $address_finded = true;
                foreach ($fields_to_find as $field_to_find) {
                    $address_finded &= ($existant_address[$field_to_find] == $address[$field_to_find]);
                }
                if ($address_finded) {
                    $id_address = $existant_address['id_address'];
                    break;
                }
            }
        }
        return $id_address;
    }
}
