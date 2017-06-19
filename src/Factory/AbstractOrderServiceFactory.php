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

namespace Oyst\Factory;

use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOrderApi;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\OrderRepository;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\OrderService;

class AbstractOrderServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return OrderService
     */
    public static function get(\Oyst $oyst, $context)
    {
        /** @var OystOrderApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ORDER,
            $oyst->getOneClickApiKey(),
            $oyst->getUserAgent(),
            $oyst->getOneClickEnvironment(),
            $oyst->getOneClickApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $logger = new PrestaShopLogger();
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $newOrderService = new OrderService(
            $context,
            $oyst
        );

        $addressRepository = new AddressRepository(Db::getInstance());
        $orderRepository = new OrderRepository(Db::getInstance());

        $newOrderService
            ->setRequester($requester)
            ->setLogger($logger)
            ->setAddressRepository($addressRepository)
            ->setOrderRepository($orderRepository)
        ;

        return $newOrderService;
    }
}
