<?php

namespace Oyst\Factory;

use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOrderApi;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\OrderRepository;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\NewOrderService;

class NewOrderServiceFactory
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
        /** @var OystOrderApi $oystOrderApi */
        $oystOrderApi = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ORDER,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment()
        );

        $oystOrderApi->setNotifyUrl($oyst->getNotifyUrl());

        $newOrderService = new NewOrderService(
            $context,
            $oyst
        );
        $orderRepository = new OrderRepository(Db::getInstance());
        $addressRepository = new AddressRepository(Db::getInstance());

        $newOrderService
            ->setOrderApi($oystOrderApi)
            ->setLogger(new PrestaShopLogger())
            ->setOrderRepository($orderRepository)
            ->setAddressRepository($addressRepository)
        ;

        return $newOrderService;
    }
}
