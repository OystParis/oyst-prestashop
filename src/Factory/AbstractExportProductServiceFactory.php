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

use \Configuration as PSConfiguration;
use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Oyst\Service\Api\Requester;
use Oyst\Service\ExportProductService;
use Oyst\Service\Logger\FileLogger;
use Oyst\Service\Serializer\ExportProductRequestParamSerializer;
use Oyst\Transformer\ProductTransformer;

class AbstractExportProductServiceFactory
{
    /**
     * Add Factory for this service due to huge redundant code used
     *
     * @param \Oyst $oyst
     * @param $context
     * @return ExportProductService
     */
    public static function get(\Oyst $oyst, $context)
    {
               /** @var OystCatalogAPI $apiClient */
        $apiClient = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $oyst->getOneClickApiKey(),
            $oyst->getUserAgent(),
            $oyst->getOneClickEnvironment(),
            $oyst->getCustomOneClickApiUrl()
        );

        $apiClient->setNotifyUrl($oyst->getNotifyUrl());

        $exportProductService = new ExportProductService(
            $context,
            $oyst
        );

        $serializer = new ExportProductRequestParamSerializer();
        $logger = new FileLogger();
        $logger->setFile(dirname(__FILE__).'/../../logs/export.log');
        $requester = new Requester($apiClient);
        $productTransformer = new ProductTransformer($context);
        $productTransformer->setLogger($logger);

        $requester
            // Avoid this if you want to store back inside the BDD and TEXT field
            ->setSerializer($serializer)
            ->setLogger($logger)
        ;

        $exportProductService
            ->setProductTransformer($productTransformer)
            ->setSerializer($serializer)
            ->setRequester($requester)
            ->setLogger($logger)
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
