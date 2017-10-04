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

use Oyst\Repository\OrderRepository;

class OystPaymentnotificationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        if (Tools::getValue('key') != Configuration::get('FC_OYST_HASH_KEY')) {
            die('Secure key is invalid');
        }

        $event_data = trim(str_replace("'", '', Tools::file_get_contents('php://input')));
        $event_data = Tools::jsonDecode($event_data, true);

        // We store the notification
        $notification_item = $event_data['notification'];
        $id_cart  = $notification_item['order_id'];
        $id_order = Order::getOrderByCartId($id_cart);

        try {
            if ($notification_item['success'] == 1) {
                switch ($notification_item['event_code']) {
                    // If authorisation succeed, we create the order
                    case OystPaymentNotification::EVENT_AUTHORISATION:
                        //$this->convertCartToOrder($notification_item, Tools::getValue('ch'), $event_data);
                        $insert   = array(
                            'id_order'   => (int) $id_order,
                            'id_cart'    => (int) $id_cart,
                            'payment_id' => pSQL($notification_item['payment_id']),
                            'event_code' => pSQL($notification_item['event_code']),
                            'event_data' => pSQL(Tools::jsonEncode($event_data)),
                            'date_event' => pSQL(Tools::substr(str_replace('T', ' ', $notification_item['event_date']), 0, 19)),
                            'date_add'   => date('Y-m-d H:i:s'),
                        );
                        Db::getInstance()->insert('oyst_payment_notification', $insert);
                        $cart = new Cart((int)$id_cart);
                        $order = new Order((int)$id_order);
                        $amount_paid = (float)($notification_item['amount']['value'] / 100);
                        if ($amount_paid != $cart->getOrderTotal())
                            $this->updateOrderStatus((int)$notification_item['order_id'], Configuration::get('PS_OS_ERROR'));
                        else
                            $this->updateOrderStatus((int)$notification_item['order_id'], Configuration::get('OYST_STATUS_FRAUD_CHECK'));
                        break;
                    // If authorisation succeed, we create the order
                    case OystPaymentNotification::EVENT_FRAUD_VALIDATION:
                        $this->updateOrderStatus((int)$notification_item['order_id'], Configuration::get('PS_OS_PAYMENT'));
                        break;
                    // If cancellation is confirmed, we cancel the order
                    case OystPaymentNotification::EVENT_CANCELLATION:
                        $this->updateOrderStatus((int)$notification_item['order_id'], Configuration::get('PS_OS_CANCELED'));
                        break;
                    // If refund is confirmed, we cancel the order
                    case OystPaymentNotification::EVENT_REFUND:
                        $oystOrderRepository = new OrderRepository(Db::getInstance());
                        $maxRefund = $oystOrderRepository->calculateOrderMaxRefund($id_cart);
                        $status = $maxRefund == 0 ? Configuration::get('PS_OS_REFUND') : Configuration::get('OYST_STATUS_PARTIAL_REFUND');

                        $this->updateOrderStatus((int)$id_cart, $status);
                        break;
                }
            } else {
                switch ($notification_item['event_code']) {
                    // If authorisation succeed, we create the order
                    case OystPaymentNotification::EVENT_FRAUD_VALIDATION:
                        $this->module->log('Payment fraud ko received, id_order : '.(int)$notification_item['order_id']);
                        $this->updateOrderStatus((int)$notification_item['order_id'], Configuration::get('PS_OS_CANCELED'));
                        break;
                }
            }
        } catch (Exception $e) {
            $this->module->log($e->getMessage());
            die(Tools::jsonEncode(array('result' => 'ko', 'error' => $e->getMessage())));
        }

        die(Tools::jsonEncode(array('result' => 'ok')));
    }

    public function updateOrderStatus($id_cart, $id_order_state)
    {
        // Get order ID
        $id_order = Order::getOrderByCartId($id_cart);

        if ($id_order > 0 && $id_order_state > 0) {
            // Create new OrderHistory
            $history = new OrderHistory();
            $history->id_order = $id_order;
            $history->id_employee = 0;
            $history->id_order_state = (int)$id_order_state;
            $history->changeIdOrderState((int)$id_order_state, $id_order);
            $history->add();
        }
    }
}
