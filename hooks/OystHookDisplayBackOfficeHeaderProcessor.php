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
        if ($order->module != $this->module->name) {
            return '';
        }

        // Ajax partial refund
        if (Tools::isSubmit('partialRefund') && isset($order)) {
            $this->partialRefundOrder($order);
        }
    }

    private function partialRefundOrder($order)
    {
        // Clean buffer
        ob_end_clean();

        $amountToRefund = $this->getAmountToRefund($order);

        if ($amountToRefund > 0) {
            // Make Oyst api call
            $result = array('error' => 'Error', 'message' => 'Transaction not found');
            $oyst_payment_notification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
            if (Validate::isLoadedObject($oyst_payment_notification)) {
                $oyst_api = new OystSDK();
                $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
                $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));

                $currency = new Currency($order->id_currency);
                $result = $oyst_api->refundRequest($oyst_payment_notification->payment_id, $amountToRefund * 100, $currency->iso_code);
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

            $response = 'success';
            if (isset($result['error'])) {
                unset($_POST['partialRefund']);
                $response = 'failure';
            }

            die(Tools::jsonEncode(array('result' => $response, 'details' => $result)));
        }
    }

    private function getAmountToRefund($order)
    {
        $id_tab = $this->context->controller->tabAccess['id_tab'];
        $tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, $id_tab);

        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $amountToRefund = $this->getAmountToRefundRecentVersion($tabAccess, $order);
        } else {
            $amountToRefund = $this->getAmountToRefundOldVersion($tabAccess);
        }

        return $amountToRefund;
    }

    private function getAmountToRefundRecentVersion($tabAccess, $order)
    {
        if ($tabAccess['edit'] != '1') {
            return 0;
        }

        $refunds = Tools::getValue('partialRefundProduct');

        if (!(Tools::isSubmit('partialRefundProduct') && $refunds && is_array($refunds))) {
            return 0;
        }

        $amount = 0;
        $order_detail_list = array();
        $full_quantity_list = array();
        foreach ($refunds as $id_order_detail => $amount_detail) {
            $quantity = Tools::getValue('partialRefundProductQuantity');
            if (!$quantity[$id_order_detail]) {
                continue;
            }

            $full_quantity_list[$id_order_detail] = (int)$quantity[$id_order_detail];

            $order_detail_list[$id_order_detail] = array(
                'quantity' => (int)$quantity[$id_order_detail],
                'id_order_detail' => (int)$id_order_detail
            );

            $order_detail = new OrderDetail((int)$id_order_detail);
            if (empty($amount_detail)) {
                $order_detail_list[$id_order_detail]['unit_price'] = (!Tools::getValue('TaxMethod') ? $order_detail->unit_price_tax_excl : $order_detail->unit_price_tax_incl);
                $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
            } else {
                $order_detail_list[$id_order_detail]['amount'] = (float)str_replace(',', '.', $amount_detail);
                $order_detail_list[$id_order_detail]['unit_price'] = $order_detail_list[$id_order_detail]['amount'] / $order_detail_list[$id_order_detail]['quantity'];
            }
            $amount += $order_detail_list[$id_order_detail]['amount'];
        }

        $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ? (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) : false;

        if ($amount == 0 && $shipping_cost_amount == 0) {
            return 0;
        }

        if ((int)Tools::getValue('refund_voucher_off') == 1) {
            $amount -= (float)Tools::getValue('order_discount_price');
        } elseif ((int)Tools::getValue('refund_voucher_off') == 2) {
            $amount = (float)Tools::getValue('refund_voucher_choose');
        }

        if ($shipping_cost_amount > 0) {
            if (!Tools::getValue('TaxMethod')) {
                $tax = new Tax();
                $tax->rate = $order->carrier_tax_rate;
                $tax_calculator = new TaxCalculator(array($tax));
                $amount += $tax_calculator->addTaxes($shipping_cost_amount);
            } else {
                $amount += $shipping_cost_amount;
            }
        }

        if ($amount > 0) {
            if (Tools::isSubmit('generateDiscountRefund')) {
                $amount = 0;
            }

            return $amount;
        } else {
            return 0;
        }
    }

    private function getAmountToRefundOldVersion($tabAccess)
    {
        if ($tabAccess['edit'] != '1' || !is_array($_POST['partialRefundProduct'])) {
            return 0;
        }

        $amount = 0;
        $order_detail_list = array();
        foreach ($_POST['partialRefundProduct'] as $id_order_detail => $amount_detail) {
            $order_detail_list[$id_order_detail]['quantity'] = (int)$_POST['partialRefundProductQuantity'][$id_order_detail];

            if (empty($amount_detail)) {
                $order_detail = new OrderDetail((int)$id_order_detail);
                $order_detail_list[$id_order_detail]['amount'] = $order_detail->unit_price_tax_incl * $order_detail_list[$id_order_detail]['quantity'];
            } else
                $order_detail_list[$id_order_detail]['amount'] = (float)$amount_detail;

            $amount += $order_detail_list[$id_order_detail]['amount'];
        }

        $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost'));
        if ($shipping_cost_amount > 0) {
            $amount += $shipping_cost_amount;
        }

        if ($amount <= 0 || Tools::isSubmit('generateDiscountRefund')) {
            return 0;
        }

        return $amount > 0 ? $amount : 0;
    }
}
