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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Oyst\Api\OystApiClientFactory;
use Oyst\Repository\OrderRepository;
use Oyst\Repository\ProductRepository;
use Oyst\Factory\AbstractFreePayPaymentServiceFactory;
use Oyst\Factory\AbstractOrderServiceFactory;
use Oyst\Classes\OystPrice;
use Oyst\Classes\Enum\AbstractOrderState;

class OystHookDisplayBackOfficeHeaderProcessor extends FroggyHookProcessor
{
    private function fetchProductContent()
    {
        if (!Module::isInstalled($this->module->name) || !Module::isEnabled($this->module->name)) {
            return '';
        }

        if (Tools::isSubmit('id_order')) {
            // Check if order has been paid with Oyst
            $order = new Order(Tools::getValue('id_order'));
            if ($order->module == $this->module->name) {
                // Partial refund
                if (Tools::isSubmit('partialRefund') && isset($order)) {
                    $this->partialRefundOrder($order);
                }
            }
            return '';
        }

        $content = '';

        if (Tools::getValue('controller') == 'AdminProducts' && Tools::isSubmit('id_product')) {
            $productRepository = new ProductRepository(Db::getInstance());
            $isProductSent = $productRepository->isProductSent(new Product(Tools::getValue('id_product')));

            /** @var Smarty_Internal_Template $template */
            $template = Context::getContext()->smarty->createTemplate(dirname(__FILE__).'/../views/templates/hook/displayAdminProduct.tpl');
            $template->assign(array(
                'isProductSent' => $isProductSent,
            ));

            $content = $template->fetch();
        }

        return $content;
    }

    private function fetchExportContent()
    {
        if (!($isPanelVisible = $this->module->getAdminPanelInformationVisibility())) {
            return '';
        }

        $oystProductRepository = new ProductRepository(Db::getInstance());
        $exportedProducts = $oystProductRepository->getExportedProduct();

        /** @var Smarty_Internal_Template $template */
        $template = Context::getContext()->smarty->createTemplate(dirname(__FILE__).'/../views/templates/hook/displayBackOfficeHeader.tpl');
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

    private function partialRefundOrder($order)
    {
        $oystOrderRepository = new OrderRepository(Db::getInstance());
        $idTab = $this->context->controller->tabAccess['id_tab'];
        $tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, $idTab);

        $amountToRefund = $oystOrderRepository->getAmountToRefund($order, $tabAccess);

        if ($amountToRefund > 0) {
            switch ($order->payment) {
                case 'FreePay':
                    $PaymentService = AbstractFreePayPaymentServiceFactory::get($this->module, $this->context);
                    break;
                case 'OneClick':
                    $PaymentService = AbstractOneClickPaymentServiceFactory::get($this->module, $this->context);
                    break;
            } 
            $orderService = AbstractOrderServiceFactory::get(
                $this->module,
                $this->context
            );            
            $guid = $orderService->getOrderRepository()->getFreePayOrderGUID($order->id);
            if ($guid) {
                $currency = new Currency($order->id_currency);
                $orderService = AbstractOrderServiceFactory::get(
                $this->module,
                $this->context
                );
                
                
                $response = $PaymentService->partialRefund($guid, new OystPrice($amountToRefund, $currency->iso_code), AbstractOrderState::REFUNDED);
                if ($response) {
                    $history = new OrderHistory();
                    $history->id_order = $order->id;
                    $history->id_employee = 0;
                    $history->id_order_state = (int)Configuration::get('OYST_STATUS_PARTIAL_REFUND_PEND');
                    $history->changeIdOrderState((int)Configuration::get('OYST_STATUS_PARTIAL_REFUND_PEND'), $order->id);
                    $history->add();
                }
            }
        }
    }
}
