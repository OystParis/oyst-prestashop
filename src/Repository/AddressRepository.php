<?php

namespace Oyst\Repository;

use Address;

class AddressRepository extends AbstractOystRepository
{
    /**
     * @param $userAddress
     * @return Address
     */
    public function findAddressUserAddress($userAddress)
    {
        $address1 = pSQL($userAddress['street']);
        $postcode = pSQL($userAddress['postcode']);
        $city = pSQL($userAddress['city']);

        $query = "
            SELECT a.id_address
            FROM ps_address a
            WHERE
              a.address1 = '$address1'
              AND a.postcode = '$postcode'
              AND a.city = '$city'
        ";

        $query = str_replace('ps_', _DB_PREFIX_, $query);
        $addressId = $this->db->getValue($query);
        return new Address($addressId);
    }
}
