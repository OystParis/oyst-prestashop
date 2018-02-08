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
use Oyst\Factory\AbstractOneClickPaymentServiceFactory;
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
                    if (!Tools::getValue('generateDiscountRefund')) {
                        $this->partialRefundOrder($order);
                    }
                }
            }
            return '';
        }

        return '';
    }

    public function run()
    {
        if (!Module::isInstalled($this->module->name) || !Module::isEnabled($this->module->name)) {
            return '';
        }

        $content = $this->fetchProductContent();

        return $content;
    }

    private function partialRefundOrder($order)
    {
        if (OystPaymentNotification::existEventCode($order->id_cart, OystPaymentNotification::EVENT_REFUND)) {
            return '';
        }
        $oystOrderRepository = new OrderRepository(Db::getInstance());
        $idTab = $this->context->controller->tabAccess['id_tab'];
        $tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, $idTab);

        $amountToRefund = $oystOrderRepository->getAmountToRefund($order, $tabAccess);

        if ($amountToRefund > 0) {
            $currency = new Currency($order->id_currency);
            $orderService = AbstractOrderServiceFactory::get(
                $this->module,
                $this->context
            );

            switch ($order->payment) {
                case 'FreePay':
                case 'Freepay':
                case 'Oyst - FreePay and 1Click':
                    $PaymentService = AbstractFreePayPaymentServiceFactory::get($this->module, $this->context);
                    $guid = $orderService->getOrderRepository()->getFreePayOrderGUID($order->id);
                    if ($guid) {
                        $response = $PaymentService->partialRefund($guid, new OystPrice($amountToRefund, $currency->iso_code), AbstractOrderState::REFUNDED);
                    }
                    $data = '';
                    break;
                case 'OneClick':
                case 'Oyst OneClick':
                    // $PaymentService = AbstractOneClickPaymentServiceFactory::get($this->module, $this->context);
                    $OrderService = AbstractOrderServiceFactory::get($this->module, $this->context);
                    $guid = $orderService->getOrderRepository()->getOrderGUID($order->id);
                    if ($guid) {
                        $oystPrice = new OystPrice($amountToRefund, $currency->iso_code);
                        $response = $OrderService->refunds($guid, $oystPrice);
                    }
                    $data = array(
                        'amount' => $oystPrice->toArray(),
                        'event_code' => OystPaymentNotification::EVENT_REFUND,
                        'order_id' => $guid
                    );
                    break;
            }

            $insert   = array(
                'id_order'   => (int)$order->id,
                'id_cart'    => (int)$order->id_cart,
                'payment_id' => pSQL($guid),
                'event_code' => pSQL(OystPaymentNotification::EVENT_REFUND),
                'event_data' => Tools::jsonEncode($data),
                'date_event' => date('Y-m-d H:i:s'),
                'date_add'   => date('Y-m-d H:i:s'),
            );

            Db::getInstance()->insert('oyst_payment_notification', $insert);

            if ($response) {
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->id_employee = 0;
                $history->id_order_state = (int)Configuration::get('OYST_STATUS_PARTIAL_REFUND');
                $history->changeIdOrderState((int)Configuration::get('OYST_STATUS_PARTIAL_REFUND'), $order->id);
                $history->add();
            }
        }
    }
}
