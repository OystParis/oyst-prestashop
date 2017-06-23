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

namespace Oyst\Service;

use Oyst\Factory\AbstractShipmentServiceFactory;
use Oyst\Repository\OneClickShipmentRepository;
use Oyst\Transformer\OneClickShipmentTransformer;
use Tools;

class OneClickShipmentService extends AbstractOystService
{
    /** @var  OneClickShipmentRepository */
    private $oneClickShipmentRepository;

    /** @var  OneClickShipmentTransformer */
    private $oneClickShipmentTransformer;

    /**
     * @return bool
     */
    public function handleShipmentRequest()
    {
        $shipments = Tools::getValue('shipments', array());
        $oystOneClickShipments = array();
        $oystShipments = array();

        foreach ($shipments as $key => $shipment) {
            if (isset($shipment['type'])) {
                $oystShipment = $this->oneClickShipmentTransformer->transformArrayToOystShipment($shipment);
                $oystShipments[] = $oystShipment;
                $oystOneClickShipments[] = $this->oneClickShipmentTransformer->transform($oystShipment);
            }
        }

        if (count($oystOneClickShipments)) {
            $shipmentService = AbstractShipmentServiceFactory::get($this->oyst, $this->context);
            $isResultOk = $shipmentService->pushShipments($oystOneClickShipments);

            if ($isResultOk) {
                $this->oneClickShipmentRepository->recordShipments($oystShipments);
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $oneClickShipmentRepository
     * @return $this
     */
    public function setOneClickShipmentRepository(OneClickShipmentRepository $oneClickShipmentRepository)
    {
        $this->oneClickShipmentRepository = $oneClickShipmentRepository;

        return $this;
    }

    /**
     * @param OneClickShipmentTransformer $oneClickShipmentTransformer
     * @return OneClickShipmentService
     */
    public function setOneClickShipmentTransformer($oneClickShipmentTransformer)
    {
        $this->oneClickShipmentTransformer = $oneClickShipmentTransformer;

        return $this;
    }
}
