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

        $oystOrderRepository = new OrderRepository(Db::getInstance());

        // Check if order has already been refunded
        $assign = array(
            'module_dir' => $this->path,
            'transaction_id' => $order->id_cart,
            'order_can_be_cancelled' => ($oystOrderRepository->orderCanBeCancelled($order->id_cart, $order->current_state) ? 1 : 0),
            'order_can_be_totally_refunded' => ($oystOrderRepository->orderCanBeTotallyRefunded($order->id_cart) ? 1 : 0),
            'order_max_refund' => $oystOrderRepository->getOrderMaxRefund($order->id_cart),
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
            $oystApi = new OystSDK();
            $oystApi->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oystApi->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $result = $oystApi->cancelOrRefundRequest($oyst_payment_notification->payment_id);
            if ($result) {
                $result = Tools::jsonDecode($result, true);
            }

            // Set refund status
            if (!isset($result['error'])) {
                $oystOrderRepository = new OrderRepository(Db::getInstance());
                $state = $oystOrderRepository->orderCanBeCancelled($order->id_cart) ? Configuration::get('OYST_STATUS_CANCELLATION_PENDING') : Configuration::get('OYST_STATUS_REFUND_PENDING');
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->id_employee = 0;
                $history->id_order_state = (int)$state;
                $history->changeIdOrderState((int)$state, $order->id);
                $history->add();
            }
        }

        die(Tools::jsonEncode(array('result' => (isset($result['error']) ? 'failure' : 'success'), 'details' => $result)));
    }
}
