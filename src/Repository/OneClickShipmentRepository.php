<?php

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
