<?php

namespace Oyst\Service;

use Oyst\Classes\OneClickShipment;

class ShipmentService extends AbstractOystService
{
    /**
     * @param OneClickShipment $shipment
     * @return bool
     */
    public function pushShipment(OneClickShipment $shipment)
    {
        $result = $this->requester->call('postShipments', [[$shipment]]);

        if (!isset($result['shipments']) || !count($result['shipments'])) {
            $this->logger->alert('No shipment(s) sent');
        }

        return isset($result['shipments']);
    }
}
