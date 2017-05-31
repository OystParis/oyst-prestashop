<?php

namespace Oyst\Factory;

use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOrderApi;
use Oyst\Repository\AddressRepository;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\NewOrderService;

class AbstractNewOrderServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return NewOrderService
     */
    static public function get(\Oyst $oyst, $context)
    {
        /** @var OystOrderApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ORDER,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment(),
            $oyst->getApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $logger = new PrestaShopLogger();
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $newOrderService = new NewOrderService(
            $context,
            $oyst
        );

        $addressRepository = new AddressRepository(Db::getInstance());

        $newOrderService
            ->setRequester($requester)
            ->setLogger($logger)
            ->setAddressRepository($addressRepository)
        ;

        return $newOrderService;
    }
}
