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

use \OystShipment;

class OneClickShipmentRepository extends AbstractOystRepository
{
    public function getShipments()
    {
        $shipments = $this->db->executeS(
            'SELECT * FROM '._DB_PREFIX_.'oyst_shipment'
        );

        return $shipments;
    }

    public function getShipment($id_carrier)
    {
    $shipment = $this->db->getRow(
            'SELECT os.* FROM `'._DB_PREFIX_.'carrier` c
            LEFT JOIN `'._DB_PREFIX_.'oyst_shipment` os ON (os.id_carrier_reference = c.id_reference)
            WHERE id_carrier = '.(int)$id_carrier
        );

        return $shipment;
    }

    /**
     * @param OystShipment[] $oystShipments
     */
    public function recordShipments(array $oystShipments)
    {
        $this->db->delete('oyst_shipment');
        foreach ($oystShipments as $oystShipment) {
            $oystShipment->save();
        }
    }
}
