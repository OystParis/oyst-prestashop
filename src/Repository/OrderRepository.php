<?php

namespace Oyst\Repository;

class OrderRepository extends AbstractOystRepository
{
    /**
     * @param $user
     * @return mixed
     */
    public function findAddressByUserInfo($user)
    {
        $address1 = $user['address']['street'];
        $postcode = $user['address']['postcode'];
        $city = $user['address']['city'];

        $query = "
            SELECT *
            FROM ps_address a
            WHERE
              a.address1 = '$address1'
              AND a.postcode = '$postcode'
              AND a.city = '$city'
        ";

        $query = str_replace('ps_', _DB_PREFIX_, $query);
        $address = $this->db->getRow($query);
        return $address;
    }
}
