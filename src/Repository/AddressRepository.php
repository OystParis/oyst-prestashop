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
            FROM "._DB_PREFIX_."address a
            WHERE
              a.address1 = '$address1'
              AND a.postcode = '$postcode'
              AND a.city = '$city'
        ";

        $addressId = $this->db->getValue($query);
        return new Address($addressId);
    }
}
