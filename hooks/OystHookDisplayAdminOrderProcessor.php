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
        if ($order->module != $this->module->name) {
            return '';
        }

        // Ajax refund
        if (Tools::getValue('subaction') == 'freepay-refund') {
            $this->refundOrder($order);
        }

        // Ajax partial refund
        if (Tools::getValue('subaction') == 'freepay-partial-refund') {
            $this->partialRefundOrder($order);
        }

        // Check if order has already been refunded
        $assign = array(
            'module_dir' => $this->path,
            'transaction_id' => $order->id_cart,
            'order_can_be_cancelled' => ($this->orderCanBeCancelled($order) ? 1 : 0),
            'order_can_be_refunded' => ($this->orderCanBeRefunded($order) ? 1 : 0),
            'max_refund' => $this->getMaxRefund($order),
            'label_cancel' => $this->module->l('Cancel order', 'oysthookdisplayadminorderprocessor'),
            'label_refund' => $this->module->l('Standard refund', 'oysthookdisplayadminorderprocessor')
        );
        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'displayAdminOrder.tpl');
    }

    public function refundOrder($order)
    {
        // Clean buffer
        ob_end_clean();

        // Make Oyst api call
        $result = array('error' => 'Error', 'message' => 'Transaction not found');
        $oyst_payment_notification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
        if (Validate::isLoadedObject($oyst_payment_notification)) {
            $oyst_api = new OystSDK();
            $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $result = $oyst_api->cancelOrRefundRequest($oyst_payment_notification->payment_id);
            if ($result) {
                $result = Tools::jsonDecode($result, true);
            }

            // Set refund status
            if (!isset($result['error'])) {
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->id_employee = 0;
                $history->id_order_state = (int)Configuration::get('PS_OS_REFUND');
                $history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), $order->id);
                $history->add();
            }
        }

        die(Tools::jsonEncode(array('result' => (isset($result['error']) ? 'failure' : 'success'), 'details' => $result)));
    }

    public function partialRefundOrder($order)
    {
    }

    public function orderCanBeCancelled($order)
    {
        $id_order_history = Db::getInstance()->getValue('
        SELECT `id_order_history`
        FROM `'._DB_PREFIX_.'order_history`
        WHERE `id_order` = '.(int)$order->id.'
        AND `id_order_state` = '.(int)Configuration::get('PS_OS_REFUND'));
        if ($id_order_history > 0) {
            return false;
        }
        return false;
    }

    public function orderCanBeRefunded($order)
    {
        $id_order_history = Db::getInstance()->getValue('
        SELECT `id_order_history`
        FROM `'._DB_PREFIX_.'order_history`
        WHERE `id_order` = '.(int)$order->id.'
        AND `id_order_state` = '.(int)Configuration::get('PS_OS_REFUND'));
        if ($id_order_history > 0) {
            return true;
        }
        return true;
    }

    public function getMaxRefund($order)
    {
        $maxRefund = 0;

        //$id_order_history = Db::getInstance()->getValue('
        //SELECT `id_order_history`
        //FROM `'._DB_PREFIX_.'order_history`
        //WHERE `id_order` = '.(int)$order->id.'
        //AND `id_order_state` = '.(int)Configuration::get('PS_OS_REFUND'));
        //if ($id_order_history > 0) {
        //    return true;
        //}

        return $maxRefund;
    }
}
