<?php

namespace Oyst\Factory;

use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOrderApi;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\OneClickService;

abstract class AbstractOneClickServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return OneClickService
     */
    static public function get(\Oyst $oyst, $context)
    {
        /** @var OystOrderApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ONECLICK,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment(),
            $oyst->getApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $logger = new PrestaShopLogger();
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $newOrderService = new OneClickService(
            $context,
            $oyst
        );

        $newOrderService
            ->setRequester($requester)
            ->setLogger($logger)
        ;

        return $newOrderService;
    }
}
