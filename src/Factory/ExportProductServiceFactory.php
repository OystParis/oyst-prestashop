<?php

namespace Oyst\Factory;

use \Configuration as PSConfiguration;
use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Oyst\Service\ExportProductService;
use Oyst\Service\Logger\PrestaShopLogger;

class ExportProductServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return ExportProductService
     */
    static public function get(\Oyst $oyst, $context)
    {
        /** @var OystCatalogAPI $oystCatalogAPI */
        $oystCatalogAPI = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment(),
            $oyst->getApiUrl()
        );

        $oystCatalogAPI->setNotifyUrl($oyst->getNotifyUrl());

        $exportProductService = new ExportProductService(
            $context,
            $oyst
        );

        $exportProductService
            ->setCatalogApi($oystCatalogAPI)
            ->setLogger(new PrestaShopLogger())
            ->setProductRepository(new ProductRepository(Db::getInstance()))
            ->setWeightUnit(PSConfiguration::get('PS_WEIGHT_UNIT'))
            ->setDimensionUnit(PSConfiguration::get('PS_CURRENCY_DEFAULT'))
        ;

        $limitedProduct = (int) getenv('OYST_EXPORT_PRODUCT_NUMBER');
        if ($limitedProduct <= 0) {
            $limitedProduct = ExportProductService::EXPORT_REGULAR_NUMBER;
        }

        $exportProductService->setLimitedProduct($limitedProduct);

        return $exportProductService;
    }
}
