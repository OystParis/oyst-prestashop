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

use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\FileLogger;
use Oyst\Service\CartService;

abstract class AbstractCartServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return CartService
     */
    public static function get(\Oyst $oyst, $context)
    {
        /** @var OystOrderApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ONECLICK,
            $oyst->getOneClickApiKey(),
            $oyst->getUserAgent(),
            $oyst->getOneClickEnvironment(),
            $oyst->getCustomOneClickApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $logger = new FileLogger();
        $logger->setFile(dirname(__FILE__).'/../../logs/cart.log');
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $cartService = new CartService($context, $oyst);

        $cartService
            ->setRequester($requester)
            ->setLogger($logger)
        ;

        return $cartService;
    }
}
