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

use Oyst\Api\OystApiClientFactory;
use Oyst\Repository\OrderRepository;

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
        if (Tools::getValue('subaction') == 'freepay-refund' && !Tools::getValue('generateDiscountRefund')) {
            $this->refundOrder($order);
        }

        $oystOrderRepository = new OrderRepository(Db::getInstance());

        switch ($order->payment) {
            case 'FreePay':
            case 'Freepay':
            case 'Oyst - FreePay and 1Click':
                $method_payment = 'FP';
                $order_can_be_totally_refunded = ($oystOrderRepository->orderCanBeTotallyRefunded($order->id_cart, $order->current_state) ? 1 : 0);
                $order_max_refund = $oystOrderRepository->getOrderMaxRefund($order->id_cart, $order->current_state);
                break;
            case 'OneClick':
            case 'Oyst OneClick':
                $method_payment = 'OC';
                $order_can_be_totally_refunded = ($oystOrderRepository->orderOystCanBeRefunded($order->id_cart, $order->current_state) ? 1 : 0);
                $order_max_refund = $oystOrderRepository->getOrderMaxRefundOC($order->id_cart, $order->current_state);
                break;
            default:
                $method_payment = null;
                $order_can_be_totally_refunded = 0;
                $order_max_refund = 0;
                break;
        }

        // Check if order has already been refunded
        $assign = array(
            'module_dir' => $this->path,
            'transaction_id' => $order->id_cart,
            'order_can_be_cancelled' => ($oystOrderRepository->orderCanBeCancelled($order->id_cart, $order->current_state) ? 1 : 0),
            'order_can_be_totally_refunded' => $order_can_be_totally_refunded,
            'order_max_refund' => $order_max_refund,
            'label_cancel' => $this->module->l('Cancel order', 'oysthookdisplayadminorderprocessor'),
            'label_refund' => $this->module->l('Standard refund', 'oysthookdisplayadminorderprocessor'),
            'label_refund_oc' => $this->module->l('Standard refund OC', 'oysthookdisplayadminorderprocessor'),
            'label_confirm_cancel' => $this->module->l('Are you sure you want to cancel this order?', 'oysthookdisplayadminorderprocessor'),
            'label_confirm_refund' => $this->module->l('Are you sure you want to totally refund this order?', 'oysthookdisplayadminorderprocessor'),
            'label_wrong_quantity' => $this->module->l('The quantity is wrong', 'oysthookdisplayadminorderprocessor'),
            'label_wrong_amount' => $this->module->l('The amount is wrong', 'oysthookdisplayadminorderprocessor'),
            'label_error' => $this->module->l('An error has occured while processing the cancellation of the order:', 'oysthookdisplayadminorderprocessor'),
            'method_payment' => $method_payment,
        );
        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'displayAdminOrder.tpl');
    }

    private function refundOrder($order)
    {
        // Clean buffer
        ob_end_clean();

        // Make Oyst api call
        $oyst_payment_notification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
        if (Validate::isLoadedObject($oyst_payment_notification)) {
            $oyst = new Oyst();
            /** @var OystPaymentApi $paymentApi */
            $paymentApi = OystApiClientFactory::getClient(
                OystApiClientFactory::ENTITY_PAYMENT,
                $oyst->getFreePayApiKey(),
                $oyst->getUserAgent(),
                $oyst->getFreePayEnvironment(),
                $oyst->getCustomFreePayApiUrl()
            );
            $response = $paymentApi->cancelOrRefund($oyst_payment_notification->payment_id);

            $success = false;
            if ($paymentApi->getLastHttpCode() == 200) {
                $success = true;
                // Set refund status
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

        die(Tools::jsonEncode(array('result' => $success ? 'success' : 'failure', 'details' => $response)));
    }
}
