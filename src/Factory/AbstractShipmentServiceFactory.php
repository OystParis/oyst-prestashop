<?php

namespace Oyst\Factory;

use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\ShipmentService;

abstract class AbstractShipmentServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @param Db $db
     * @return ShipmentService
     */
    static public function get(\Oyst $oyst, $context, Db $db = null)
    {
        /** @var OystCatalogApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $oyst->getOneClickApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment(),
            $oyst->getApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $logger = new PrestaShopLogger();
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $shipmentService = new ShipmentService($context, $oyst);

        $shipmentService
            ->setRequester($requester)
            ->setLogger($logger)
        ;

        return $shipmentService;
    }
}
