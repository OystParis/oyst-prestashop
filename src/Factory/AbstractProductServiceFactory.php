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
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\FileLogger;
use Oyst\Service\ProductService;
use Oyst\Transformer\ProductTransformer;

abstract class AbstractProductServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return ProductService
     */
    public static function get(\Oyst $oyst, $context, Db $db)
    {
        /** @var OystCatalogApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $oyst->getOneClickApiKey(),
            $oyst->getUserAgent(),
            $oyst->getOneClickEnvironment(),
            $oyst->getCustomOneClickApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $productTransformer = new ProductTransformer($context);
        $logger = new FileLogger();
        $logger->setFile(dirname(__FILE__).'/../../logs/product.log');
        $requester = new Requester($apiClient);
        $requester->setLogger($logger);

        $productService = new ProductService($context, $oyst);

        $productService
            ->setRequester($requester)
            ->setLogger($logger)
            ->setProductRepository(new ProductRepository($db))
            ->setProductTransformer($productTransformer)
        ;

        return $productService;
    }
}
