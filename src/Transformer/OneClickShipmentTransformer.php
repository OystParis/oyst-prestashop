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

namespace Oyst\Transformer;

use Carrier;
use Oyst\Classes\OneClickShipment;
use Oyst\Classes\OystCarrier;
use Oyst\Classes\ShipmentAmount;
use \OystShipment;

/**
 * Class ProductTransformer
 */
class OneClickShipmentTransformer extends AbstractTransformer
{

    /**
     * @param OystShipment $oystShipment
     *
     * @return OneClickShipment
     */
    public function transform($oystShipment)
    {
        $oneClickShipment = new OneClickShipment();
        $carrier = Carrier::getCarrierByReference($oystShipment->getIdCarrierReference());
        $oystCarrier = new OystCarrier(
            $oystShipment->getIdCarrierReference(),
            $carrier->name,
            $oystShipment->getType()
        );

        $shipmentAmount = new ShipmentAmount(
            $oystShipment->getAmountFollower(),
            $oystShipment->getAmountLeader(),
            $oystShipment->getCurrency()
        );

        $oneClickShipment->setCarrier($oystCarrier);
        $oneClickShipment->setAmount($shipmentAmount);
        $oneClickShipment->setFreeShipping($oystShipment->getFreeShipping());
        $oneClickShipment->setPrimary($oystShipment->isPrimary());
        $oneClickShipment->setDelay($oystShipment->getDelay() * 24);
        $oneClickShipment->setZones(json_decode($oystShipment->getZones()));

        return $oneClickShipment;
    }

    /**
     * @param OneClickShipment $oneClickShipment
     *
     * @return OystShipment
     */
    public function reverseTransform($oneClickShipment)
    {
        $oystShipment = new OystShipment();

        $oystShipment
            ->setIdCarrierReference($oneClickShipment->getCarrier()->getId())
            ->setPrimary($oneClickShipment->getPrimary())
            ->setType($oneClickShipment->getCarrier()->getType())
            ->setDelay($oneClickShipment->getDelay() > 0 ? $oneClickShipment->getDelay() / 24 : 0)
            ->setAmountLeader($oneClickShipment->getPrimary((float) ($oneClickShipment->getAmount()->getAmountLeader() > 0 ?
                $oneClickShipment->getAmount()->getAmountLeader() / 100 : 0)))
            ->setAmountFollower((float) ($oneClickShipment->getAmount()->getAmountFollower() > 0 ?
                $oneClickShipment->getAmount()->getAmountFollower() / 100 : 0))
            ->setFreeShipping((float) ($oneClickShipment->getFreeShipping() > 0 ?
                $oneClickShipment->getFreeShipping() / 100 : 0.0))
            ->setCurrency($oneClickShipment->getAmount()->getCurrency())
            ->setZones(json_encode($oneClickShipment->getZones()))
        ;

        return $oystShipment;
    }

    /**
     * @param $shipment
     * @return OystShipment
     */
    public function transformArrayToOystShipment($shipment)
    {
        $oystShipment = new OystShipment();
        $oystShipment->setIdCarrierReference($shipment['id_carrier'])
            ->setPrimary(isset($shipment['primary']) && $shipment['primary'])
            ->setType($shipment['type'])
            ->setDelay($shipment['delay'])
            ->setAmountLeader($shipment['amount_leader'])
            ->setAmountFollower($shipment['amount_follower'])
            ->setFreeShipping($shipment['free_shipping'])
            ->setCurrency('EUR')
            ->setZones(json_encode(array('FR')))
        ;

        return $oystShipment;
    }
}
