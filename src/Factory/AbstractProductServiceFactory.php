<?php

namespace Oyst\Factory;

use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\PrestaShopLogger;
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
    static public function get(\Oyst $oyst, $context, Db $db)
    {
        /** @var OystCatalogApi $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment(),
            $oyst->getApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $productTransformer = new ProductTransformer($context);
        $logger = new PrestaShopLogger();
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
