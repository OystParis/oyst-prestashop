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
class OystHookActionProductUpdateProcessor extends FroggyHookProcessor
{
    /**
     *
     */
    public function run()
    {
        // If combination is set, it will use this same process on its own
        if (Tools::isSubmit('id_product_attribute') && Tools::getValue('id_product_attribute') > 0) {
            return true;
        }

        if (Configuration::get('OYST_ONE_CLICK_FEATURE_STATE')) {
            $product = $this->params['product'];
            $productService = \Oyst\Factory\AbstractProductServiceFactory::get($this->module, $this->context, Db::getInstance());
            $succeed = $productService->sendProduct($product);

            // if (!$succeed) {
            //     $this->context->controller->errors[] = 'Can\'t synchronise product to oyst (while update product):';
            //     $this->context->controller->errors[] = $productService->getRequester()->getApiClient()->getLastError();
            // }
        }
    }
}
