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
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayBackOfficeHeaderProcessor extends FroggyHookProcessor
{
    public function run()
    {
        // Check if order has been paid with Oyst
        $order = new Order(Tools::getValue('id_order'));
        if ($order->module == $this->module->name) {
            // Partial refund
            if (Tools::isSubmit('partialRefund') && isset($order)) {
                $this->partialRefundOrder($order);
            }
        }
    }

    private function partialRefundOrder($order)
    {
        $oystOrderRepository = new OrderRepository(Db::getInstance());
        $idTab = $this->context->controller->tabAccess['id_tab'];
        $tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, $idTab);

        $amountToRefund = $oystOrderRepository->getAmountToRefund($order, $tabAccess);

        if ($amountToRefund > 0) {
            // Make Oyst api call
            $result = array('error' => 'Error', 'message' => 'Transaction not found');
            $oystPaymentNotification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
            if (Validate::isLoadedObject($oystPaymentNotification)) {
                $oystApi = new OystSDK();
                $oystApi->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
                $oystApi->setApiKey(Configuration::get('FC_OYST_API_KEY'));

                $currency = new Currency($order->id_currency);
                $result = $oystApi->cancelOrRefundRequest($oystPaymentNotification->payment_id, $amountToRefund * 100, $currency->iso_code);
                if ($result) {
                    $result = Tools::jsonDecode($result, true);
                }

                // Set refund status
                if (!isset($result['error'])) {
                    $history = new OrderHistory();
                    $history->id_order = $order->id;
                    $history->id_employee = 0;
                    $history->id_order_state = (int)Configuration::get('OYST_STATUS_PARTIAL_REFUND_PENDING');
                    $history->changeIdOrderState((int)Configuration::get('OYST_STATUS_PARTIAL_REFUND_PENDING'), $order->id);
                    $history->add();
                }
            }

            if (isset($result['error'])) {
                unset($_POST['partialRefund']);

                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders').'&vieworder&id_order='.$order->id);
            }
        }
    }
}
