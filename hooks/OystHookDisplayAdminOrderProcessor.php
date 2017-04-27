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

        // Check if order has already been refunded
        $assign = array(
            'module_dir' => $this->path,
            'transaction_id' => $order->id_cart,
            'order_can_be_cancelled' => ($this->orderCanBeCancelled($order) ? 1 : 0),
            'order_can_be_totally_refunded' => ($this->orderCanBeTotallyRefunded($order) ? 1 : 0),
            'order_max_refund' => $this->getOrderMaxRefund($order),
            'label_cancel' => $this->module->l('Cancel order', 'oysthookdisplayadminorderprocessor'),
            'label_refund' => $this->module->l('Standard refund', 'oysthookdisplayadminorderprocessor')
        );
        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'displayAdminOrder.tpl');
    }

    private function refundOrder($order)
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

    private function orderCanBeCancelled($order)
    {
        // The order must have an AUTHORISATION event and no CAPTURE/CANCELLATION event
        $sql = 'SELECT COUNT(DISTINCT(opn.`id_oyst_payment_notification`))'
            .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn'
            .' WHERE opn.`id_cart` = '.(int) $order->id_cart
            .' AND opn.`event_code` = "'.OystPaymentNotification::EVENT_AUTHORISATION.'"'
            .' AND opn.`id_cart` NOT IN ('
                .'SELECT opn_bis.`id_cart`'
                .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn_bis'
                .' WHERE opn_bis.`event_code` = "'.OystPaymentNotification::EVENT_CAPTURE.'"'
                .' OR opn_bis.`event_code` = "'.OystPaymentNotification::EVENT_CANCELLATION.'"'
            .')';

        $result = Db::getInstance()->getValue($sql);

        return $result > 0;
    }

    private function orderCanBeTotallyRefunded($order)
    {
        // The order must have an AUTHORISATION event and no CAPTURE/CANCELLATION event
        $sql = 'SELECT COUNT(DISTINCT(opn.`id_oyst_payment_notification`))'
            .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn'
            .' WHERE opn.`id_cart` = '.(int) $order->id_cart
            .' AND opn.`event_code` = "'.OystPaymentNotification::EVENT_CAPTURE.'"'
            .' AND opn.`id_cart` NOT IN ('
                .'SELECT opn_bis.`id_cart`'
                .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn_bis'
            .' WHERE opn_bis.`event_code` = "'.OystPaymentNotification::EVENT_REFUND.'"'
                .' OR opn_bis.`event_code` = "'.OystPaymentNotification::EVENT_CANCELLATION.'"'
            .')';

        $result = Db::getInstance()->getValue($sql);

        return $result > 0;
    }

    private function getOrderMaxRefund($order)
    {
        $maxRefund = 0;

        // The order must have a CAPTURE event and no CANCELLATION event
        $sql = 'SELECT opn.`event_data`'
            .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn'
            .' WHERE opn.`id_cart` = '.(int) $order->id_cart
            .' AND opn.`event_code` = "'.OystPaymentNotification::EVENT_CAPTURE.'"'
            .' AND opn.`id_cart` NOT IN ('
                .'SELECT opn_bis.`id_cart`'
                .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn_bis'
                .' WHERE opn_bis.`event_code` = "'.OystPaymentNotification::EVENT_CANCELLATION.'"'
            .')';

        // Return data of the CAPTURE event
        $result = Db::getInstance()->getValue($sql);

        if ($result) {
            $result      = json_decode($result, true);
            $totalAmount = $result['notification']['amount']['value'] / 100;
            $maxRefund   = $this->calculateMaxRefund($order, $totalAmount);
        }

        return $maxRefund;
    }

    private function calculateMaxRefund($order, $totalAmount)
    {
        $maxRefund = $totalAmount;

        $sql = 'SELECT opn.`event_data`'
            .' FROM `'._DB_PREFIX_.'oyst_payment_notification` opn'
            .' WHERE opn.`id_cart` = '.(int) $order->id_cart
            .' AND opn.`event_code` = "'.OystPaymentNotification::EVENT_REFUND.'"';

        $result = Db::getInstance()->query($sql);

        while ($row = Db::getInstance()->nextRow($result)) {
            $data       = json_decode($row['event_data'], true);
            $maxRefund -= $data['notification']['amount']['value'] / 100;
        }

        return $maxRefund;
    }
}
