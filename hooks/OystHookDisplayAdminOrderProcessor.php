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

class OystHookDisplayAdminOrderProcessor extends FroggyHookProcessor
{
    public function run()
    {
        // Check if order has been paid with Oyst
        $order = new Order(Tools::getValue('id_order'));
        if ($order->module != 'oyst') {
            return '';
        }

        // Ajax refund
        if (Tools::getValue('subaction') == 'freepay-refund') {
            $this->refundOrder($order);
        }

        $assign = array(
            'module_dir' => $this->path,
            'transaction_id' => $order->id_cart,
        );
        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'displayAdminOrder.tpl');
    }

    public function refundOrder($order)
    {
        // Clean buffer
        ob_end_clean();

        // Make Oyst api call
        $result = array('statusCode' => 404, 'error' => 'Transaction not found');
        $oyst_payment_notification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
        if (Validate::isLoadedObject($oyst_payment_notification)) {
            $oyst_api = new OystSDK();
            $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $result = $oyst_api->cancelRefundRequest($oyst_payment_notification->payment_id);
            if ($result) {
                $result = Tools::jsonDecode($result);
            }

            // Set refund status
            $history = new OrderHistory();
            $history->id_order = $order->id;
            $history->id_employee = 0;
            $history->id_order_state = (int)Configuration::get('PS_OS_REFUND');
            $history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), $order->id);
            $history->add();
        }

        die(Tools::jsonEncode(array('result' => (isset($result['statusCode']) && $result['statusCode'] == 200 ? 'success' : 'failure'), 'details' => $result)));
    }
}
