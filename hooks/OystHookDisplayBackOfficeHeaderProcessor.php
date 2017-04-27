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
            var_dump("qsgkdhfgmksdg");die;
            $this->partialRefundOrder($order);
        }
    }

    private function partialRefundOrder($order)
    {
        // Clean buffer
        ob_end_clean();

        $executeRefund = $this->canExecuteRefund($order);

        if (!$executeRefund) {
            unset($_POST['partialRefund']);

            return '';
        }

        // Make Oyst api call
        $result = array('error' => 'Error', 'message' => 'Transaction not found');
        $oyst_payment_notification = OystPaymentNotification::getOystPaymentNotificationFromCartId($order->id_cart);
        if (Validate::isLoadedObject($oyst_payment_notification)) {
            $oyst_api = new OystSDK();
            $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $result = $oyst_api->refundRequest($oyst_payment_notification->payment_id, $value, $currency);
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

    private function canExecuteRefund($order)
    {
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            if ($this->tabAccess['edit'] == '1') {
                if (Tools::isSubmit('partialRefundProduct') && ($refunds = Tools::getValue('partialRefundProduct')) && is_array($refunds)) {
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
                        if (!$order->hasBeenDelivered() || ($order->hasBeenDelivered() && Tools::isSubmit('reinjectQuantities')) && $order_detail_list[$id_order_detail]['quantity'] > 0) {
                            $this->reinjectQuantity($order_detail, $order_detail_list[$id_order_detail]['quantity']);
                        }
                    }

                    $shipping_cost_amount = (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) ? (float)str_replace(',', '.', Tools::getValue('partialRefundShippingCost')) : false;

                    if ($amount == 0 && $shipping_cost_amount == 0) {
                        if (!empty($refunds)) {
                            $this->errors[] = Tools::displayError('Please enter a quantity to proceed with your refund.');
                        } else {
                            $this->errors[] = Tools::displayError('Please enter an amount to proceed with your refund.');
                        }
                        return false;
                    }

                    $choosen = false;
                    $voucher = 0;

                    if ((int)Tools::getValue('refund_voucher_off') == 1) {
                        $amount -= $voucher = (float)Tools::getValue('order_discount_price');
                    } elseif ((int)Tools::getValue('refund_voucher_off') == 2) {
                        $choosen = true;
                        $amount = $voucher = (float)Tools::getValue('refund_voucher_choose');
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

                    $order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
                    if (Validate::isLoadedObject($order_carrier)) {
                        $order_carrier->weight = (float)$order->getTotalWeight();
                        if ($order_carrier->update()) {
                            $order->weight = sprintf("%.3f " . Configuration::get('PS_WEIGHT_UNIT'), $order_carrier->weight);
                        }
                    }

                    if ($amount >= 0) {
                        if (!OrderSlip::create($order, $order_detail_list, $shipping_cost_amount, $voucher, $choosen,
                            (Tools::getValue('TaxMethod') ? false : true))
                        ) {
                            $this->errors[] = Tools::displayError('You cannot generate a partial credit slip.');
                        } else {
                            Hook::exec('actionOrderSlipAdd', array('order' => $order, 'productList' => $order_detail_list, 'qtyList' => $full_quantity_list), null, false, true, false, $order->id_shop);
                            $customer = new Customer((int)($order->id_customer));
                            $params['{lastname}'] = $customer->lastname;
                            $params['{firstname}'] = $customer->firstname;
                            $params['{id_order}'] = $order->id;
                            $params['{order_name}'] = $order->getUniqReference();
                            @Mail::Send(
                                (int)$order->id_lang,
                                'credit_slip',
                                Mail::l('New credit slip regarding your order', (int)$order->id_lang),
                                $params,
                                $customer->email,
                                $customer->firstname . ' ' . $customer->lastname,
                                null,
                                null,
                                null,
                                null,
                                _PS_MAIL_DIR_,
                                true,
                                (int)$order->id_shop
                            );
                        }

                        foreach ($order_detail_list as &$product) {
                            $order_detail = new OrderDetail((int)$product['id_order_detail']);
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                StockAvailable::synchronize($order_detail->product_id);
                            }
                        }

                        // Generate voucher
                        if (Tools::isSubmit('generateDiscountRefund') && !count($this->errors) && $amount > 0) {
                            $cart_rule = new CartRule();
                            $cart_rule->description = sprintf($this->l('Credit slip for order #%d'), $order->id);
                            $language_ids = Language::getIDs(false);
                            foreach ($language_ids as $id_lang) {
                                // Define a temporary name
                                $cart_rule->name[$id_lang] = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);
                            }

                            // Define a temporary code
                            $cart_rule->code = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);
                            $cart_rule->quantity = 1;
                            $cart_rule->quantity_per_user = 1;

                            // Specific to the customer
                            $cart_rule->id_customer = $order->id_customer;
                            $now = time();
                            $cart_rule->date_from = date('Y-m-d H:i:s', $now);
                            $cart_rule->date_to = date('Y-m-d H:i:s', strtotime('+1 year'));
                            $cart_rule->partial_use = 1;
                            $cart_rule->active = 1;

                            $cart_rule->reduction_amount = $amount;
                            $cart_rule->reduction_tax = $order->getTaxCalculationMethod() != PS_TAX_EXC;
                            $cart_rule->minimum_amount_currency = $order->id_currency;
                            $cart_rule->reduction_currency = $order->id_currency;

                            if (!$cart_rule->add()) {
                                $this->errors[] = Tools::displayError('You cannot generate a voucher.');
                            } else {
                                // Update the voucher code and name
                                foreach ($language_ids as $id_lang) {
                                    $cart_rule->name[$id_lang] = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);
                                }
                                $cart_rule->code = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);

                                if (!$cart_rule->update()) {
                                    $this->errors[] = Tools::displayError('You cannot generate a voucher.');
                                } else {
                                    $currency = $this->context->currency;
                                    $customer = new Customer((int)($order->id_customer));
                                    $params['{lastname}'] = $customer->lastname;
                                    $params['{firstname}'] = $customer->firstname;
                                    $params['{id_order}'] = $order->id;
                                    $params['{order_name}'] = $order->getUniqReference();
                                    $params['{voucher_amount}'] = Tools::displayPrice($cart_rule->reduction_amount, $currency, false);
                                    $params['{voucher_num}'] = $cart_rule->code;
                                    @Mail::Send((int)$order->id_lang, 'voucher', sprintf(Mail::l('New voucher for your order #%s', (int)$order->id_lang), $order->reference),
                                        $params, $customer->email, $customer->firstname . ' ' . $customer->lastname, null, null, null,
                                        null, _PS_MAIL_DIR_, true, (int)$order->id_shop);
                                }
                            }
                        }
                    } else {
                        if (!empty($refunds)) {
                            $this->errors[] = Tools::displayError('Please enter a quantity to proceed with your refund.');
                        } else {
                            $this->errors[] = Tools::displayError('Please enter an amount to proceed with your refund.');
                        }
                    }

                    // Redirect if no errors
                    if (!count($this->errors)) {
                        Tools::redirectAdmin(self::$currentIndex . '&id_order=' . $order->id . '&vieworder&conf=30&token=' . $this->token);
                    }
                } else {
                    $this->errors[] = Tools::displayError('The partial refund data is incorrect.');
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }
        } else {
            if ($this->tabAccess['edit'] == '1') {
                if (is_array($_POST['partialRefundProduct'])) {
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
                    if ($shipping_cost_amount > 0)
                        $amount += $shipping_cost_amount;

                    if ($amount > 0) {
                        if (!OrderSlip::createPartialOrderSlip($order, $amount, $shipping_cost_amount, $order_detail_list))
                            $this->errors[] = Tools::displayError('Cannot generate partial credit slip');

                        // Generate voucher
                        if (Tools::isSubmit('generateDiscountRefund') && !count($this->errors)) {
                            $cart_rule = new CartRule();
                            $cart_rule->description = sprintf($this->l('Credit Slip for order #%d'), $order->id);
                            $languages = Language::getLanguages(false);
                            foreach ($languages as $language)
                                // Define a temporary name
                                $cart_rule->name[$language['id_lang']] = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);

                            // Define a temporary code
                            $cart_rule->code = sprintf('V0C%1$dO%2$d', $order->id_customer, $order->id);
                            $cart_rule->quantity = 1;
                            $cart_rule->quantity_per_user = 1;

                            // Specific to the customer
                            $cart_rule->id_customer = $order->id_customer;
                            $now = time();
                            $cart_rule->date_from = date('Y-m-d H:i:s', $now);
                            $cart_rule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25)); /* 1 year */
                            $cart_rule->active = 1;

                            $cart_rule->reduction_amount = $amount;
                            $cart_rule->reduction_tax = true;
                            $cart_rule->minimum_amount_currency = $order->id_currency;
                            $cart_rule->reduction_currency = $order->id_currency;

                            if (!$cart_rule->add())
                                $this->errors[] = Tools::displayError('Cannot generate voucher');
                            else {
                                // Update the voucher code and name
                                foreach ($languages as $language)
                                    $cart_rule->name[$language['id_lang']] = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);
                                $cart_rule->code = sprintf('V%1$dC%2$dO%3$d', $cart_rule->id, $order->id_customer, $order->id);

                                if (!$cart_rule->update())
                                    $this->errors[] = Tools::displayError('Cannot generate voucher');
                                else {
                                    $currency = $this->context->currency;
                                    $params['{voucher_amount}'] = Tools::displayPrice($cart_rule->reduction_amount, $currency, false);
                                    $params['{voucher_num}'] = $cart_rule->code;
                                    $customer = new Customer((int)$order->id_customer);
                                    @Mail::Send((int)$order->id_lang, 'voucher', sprintf(Mail::l('New voucher regarding your order %s', (int)$order->id_lang), $order->reference),
                                        $params, $customer->email, $customer->firstname . ' ' . $customer->lastname, null, null, null,
                                        null, _PS_MAIL_DIR_, true, (int)$order->id_shop);
                                }
                            }
                        }
                    } else
                        $this->errors[] = Tools::displayError('You have to write an amount if you want to do a partial credit slip');

                    // Redirect if no errors
                    if (!count($this->errors))
                        Tools::redirectAdmin(self::$currentIndex . '&id_order=' . $order->id . '&vieworder&conf=30&token=' . $this->token);
                } else
                    $this->errors[] = Tools::displayError('Partial refund data is incorrect');
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete here.');
            }
        }
    }
}
