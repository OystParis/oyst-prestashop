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


use Oyst\Repository\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayBackOfficeHeaderProcessor extends FroggyHookProcessor
{
    private function fetchProductContent()
    {
        $content = '';

        if (Tools::getValue('controller') == 'AdminProducts' && Tools::isSubmit('id_product')) {
            $productRepository = new ProductRepository(Db::getInstance());
            $isProductSent = $productRepository->isProductSent(new Product(Tools::getValue('id_product')));

            /** @var Smarty_Internal_Template $template */
            $template = Context::getContext()->smarty->createTemplate(__DIR__.'/../views/templates/hook/displayAdminProduct.tpl');
            $template->assign(array(
                'isProductSent' => $isProductSent,
            ));

            $content = $template->fetch();
        }

        return $content;
    }


    private function fetchExportContent()
    {
        $oystProductRepository = new ProductRepository(Db::getInstance());
        $exportedProducts = $oystProductRepository->getExportedProduct();

        /** @var Smarty_Internal_Template $template */
        $template = Context::getContext()->smarty->createTemplate(__DIR__.'/../views/templates/hook/displayBackOfficeHeader.tpl');
        $exportDate = $this->module->getRequestedCatalogDate();
        $template->assign(array(
            'marginRequired' => version_compare(_PS_VERSION_, '1.5', '>'),
            'OYST_REQUESTED_CATALOG_DATE' => $exportDate ? $exportDate->format(Context::getContext()->language->date_format_full) : false,
            'OYST_IS_EXPORT_STILL_RUNNING' => $this->module->isCatalogExportStillRunning(),
            'exportedProducts' => $exportedProducts,
            'displayPanel' => $this->module->getAdminPanelInformationVisibility(),
        ));

        $content = $template->fetch();

        return $content;
    }

    public function run()
    {
        if (!ModuleCore::isInstalled($this->module->name) || !ModuleCore::isEnabled($this->module->name)) {
            return '';
        }

        $content = $this->fetchExportContent();
        $content .= $this->fetchProductContent();

        return $content;
    }
}
