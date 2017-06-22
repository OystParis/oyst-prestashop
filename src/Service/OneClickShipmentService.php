<?php

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
