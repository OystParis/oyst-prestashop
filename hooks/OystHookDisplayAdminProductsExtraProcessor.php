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
//use Oyst\Repository\OneClickProductRepository;
use Oyst\Repository\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayAdminProductsExtraProcessor extends FroggyHookProcessor
{
    public function run()
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product);
        $currentOneClickApiKey = $this->module->getOneClickApiKey();
        $isCurrentOneClickApiKeyValid = Tools::strlen($currentOneClickApiKey) == 64;

        //$oneClickProductRepository = new OneClickProductRepository(Db::getInstance());
        $productRepository = new ProductRepository(Db::getInstance());

        $this->smarty->assign('oyst', array(
            'currentOneClickApiKeyValid' => $isCurrentOneClickApiKeyValid,
            'OYST_ONE_CLICK_FEATURE_STATE' => Configuration::get('OYST_ONE_CLICK_FEATURE_STATE'),
            'product_is_associated_to_shop' => $product->isAssociatedToShop(),
            'active_oneclick' => $productRepository->getActive($id_product)
        ));

        return $this->module->fcdisplay(__FILE__, 'displayAdminProductExtra.tpl');
    }
}
