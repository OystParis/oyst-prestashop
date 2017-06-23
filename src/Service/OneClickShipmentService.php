<?php

namespace Oyst\Service;

use Oyst\Factory\AbstractShipmentServiceFactory;
use Oyst\Repository\OneClickShipmentRepository;
use Oyst\Transformer\OneClickShipmentTransformer;
use OystShipment;
use Tools;

/**
 * TODO: Should be merged with ShipmentService
 */
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
     * @return bool
     */
    public function updateShipments()
    {
        $shipmentsInfo = $this->oneClickShipmentRepository->getShipments();
        $oneClickShipments = array();
        foreach ($shipmentsInfo as $shipmentInfo) {
            $oneClickShipments[] = $this->oneClickShipmentTransformer->transform(
                new OystShipment($shipmentInfo['id_oyst_shipment'])
            );
        }

        $shipmentService = AbstractShipmentServiceFactory::get($this->oyst, $this->context);
        $shipmentService->pushShipments($oneClickShipments);

        return $shipmentService->getRequester()->getApiClient()->getLastHttpCode() == "200";
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
