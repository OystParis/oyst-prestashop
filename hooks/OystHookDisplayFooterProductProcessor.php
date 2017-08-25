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
use Oyst\Repository\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayFooterProductProcessor extends FroggyHookProcessor
{
    public function run()
    {
        $product = new Product(Tools::getValue('id_product'));
        if (!Validate::isLoadedObject($product)) {
            return '';
        }

        $productRepository = new ProductRepository(Db::getInstance());
        $exportedProducts = $productRepository->getExportedFromProduct($product);

        if (!$exportedProducts) {
            return '';
        }

        $synchronizedCombination = array();
        foreach ($exportedProducts as $exportedProduct) {
            if ($exportedProduct['hasBeenExported']) {
                $stockAvailable = new StockAvailable(
                    StockAvailable::getStockAvailableIdByProductId($product->id, $exportedProduct['productAttributeId'])
                );
                $synchronizedCombination[$exportedProduct['productAttributeId']] = array(
                    'quantity' => $stockAvailable->quantity
                );
            }
        }

        $this->smarty->assign(array(
            'shopUrl' => trim(Tools::getShopDomainSsl(true).__PS_BASE_URI__, '/'),
            'product' => $product,
            'synchronizedCombination' => $synchronizedCombination,
            'stockManagement' => Configuration::get('PS_STOCK_MANAGEMENT'),
            'oneClickActivated' => (int) Configuration::get('OYST_ONE_CLICK_FEATURE_STATE'),
        ));
        $this->context->controller->addJS(array(
            $this->path.'views/js/OystOneClick.js',
            trim($this->module->getOneClickUrl(), '/').'/1click/script/script.min.js',
        ));
        $this->context->controller->addCSS(array(
            $this->path.'views/css/oyst.css',
        ));
        return $this->module->fcdisplay(__FILE__, 'displayFooterProduct.tpl');
    }
}
