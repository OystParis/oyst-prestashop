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

namespace Oyst\Repository;

use Address;
use Customer;

class AddressRepository extends AbstractOystRepository
{
    /**
     * @param $address
     * @return Address
     */
    public function findAddress($address, Customer $customer)
    {
        $address1 = pSQL($address['street']);
        $postcode = pSQL($address['postcode']);
        $city = pSQL($address['city']);
        $firstname = pSQL($address['first_name']);
        $lastname = pSQL($address['last_name']);

        $query = "
            SELECT a.id_address
            FROM "._DB_PREFIX_."address a
            INNER JOIN "._DB_PREFIX_."customer c ON a.id_customer = c.id_customer
            WHERE
              a.address1 = '".$address1."'
              AND a.postcode = '".$postcode."'
              AND a.city = '".$city."'
              AND a.firstname = '".$firstname."'
              AND a.lastname = '".$lastname."'
              AND c.id_customer = ".$customer->id."
        ";

        if (isset($address['name'])) {
            $query .= ' AND a.alias = "'.pSQL($address['name']).'"';
        }

        $addressId = (int)$this->db->getValue($query);
        if (is_numeric($addressId) && $addressId > 0) {
            return new Address($addressId);
        } else {
            return false;
        }
    }
}
