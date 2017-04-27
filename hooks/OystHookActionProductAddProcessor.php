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

/*
 * Security
 */
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\ProductService;
use Oyst\Transformer\ProductTransformer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OystHookActionProductAddProcessor
 */
class OystHookActionProductAddProcessor extends FroggyHookProcessor
{
    /**
     *
     */
    public function run()
    {
        $product = $this->params['product'];

        /** @var OystCatalogApi $catalogApi */
        $catalogApi = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $this->module->getApiKey(),
            $this->module->getUserAgent(),
            $this->module->getEnvironment()
        );

        // Todo: Maybe prevent user if the product has not been sent ?
        (new ProductService(Context::getContext(), $this->module))
            ->setProductTransformer(new ProductTransformer(Context::getContext()))
            ->setCatalogApi($catalogApi)
            ->setProductRepository(new ProductRepository(Db::getInstance()))
            ->setLogger(new PrestaShopLogger())
            ->sendNewProduct($product, new Combination())
        ;
    }
}
