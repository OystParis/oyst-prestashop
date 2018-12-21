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

namespace Oyst\Controller;

use Db;
use Oyst;
use Order;
use Context;
use Configuration;
use Oyst\Classes\Enum\AbstractOrderState;
use Oyst\Factory\AbstractOrderServiceFactory;

class OrderController extends AbstractOystController
{
    public function createNewOrderAction()
    {
        $json = $this->request->getJson();

        if ($json) {
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderService = AbstractOrderServiceFactory::get($oyst, $context);
            $orderId = $json['data']['order_id'];
            file_put_contents(__DIR__.'/../../../debug.log', date('y-m-d H:i:s')." - [OrderController.php:43] Oyst json data :".print_r($json, true)."\r\n", FILE_APPEND);
            $responseData = $orderService->requestCreateNewOrder($orderId);

            $order_id = $orderService->getOrderRepository()->getOrderId($orderId);
            if ($order_id) {
                $order = new Order($order_id);
            }


            $order_info = $orderService->getOrderInfo($orderId);
            $status = $order_info['order']['status'];

            $orderState = '';

            if (count($status) == 2) {
                $orderService->updateOrderStatusPresta($orderId, 'PS_OS_PAYMENT', $json);
                if ($responseData['state']) {
                    $orderService->updateOrderStatus($orderId, AbstractOrderState::ACCEPTED);
                    $orderState = AbstractOrderState::ACCEPTED;
                    $this->logger->info(sprintf("Info creating order: [%s]", json_encode($responseData['state'])));
                } else {
                    $orderService->updateOrderStatus($orderId, AbstractOrderState::DENIED);
                    $orderState = AbstractOrderState::DENIED;
                    $this->logger->critical(sprintf("Error creating order: [%s]", json_encode($responseData['error'])));
                }
            } else {
                $orderService->addNewPrivateMessage($order->id, "Order has been already accepted.<br />".json_encode($status));
                $orderState = AbstractOrderState::PAYMENT_FAILED;
            }

            $insert = array(
                'id_order'   => $order_id ? (int)$order_id : 0,
                'id_cart'    => $order_id ? (int)$order->id_cart : 0,
                'payment_id' => pSQL($orderId),
                'event_code' => 'order.patch',
                'event_data' => json_encode($json),
                'response'   => json_encode($responseData),
                'status'     => $orderState,
                'date_event' => date('Y-m-d H:i:s'),
                'date_add'   => date('Y-m-d H:i:s'),
                'date_upd'   => date('Y-m-d H:i:s'),
            );
            Db::getInstance()->insert('oyst_payment_notification', $insert);

            $this->respondAsJson($responseData);
        } else {
            header("HTTP/1.1 400 Bad Request");
        }
    }

    public function updateOrderStatus()
    {
        $status_mapping = array(
            'suspected_fraud' => 'OYST_STATUS_FRAUD',
        );

        $json = $this->request->getJson();

        if ($json) {
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderService = AbstractOrderServiceFactory::get($oyst, $context);
            $orderId = $json['data']['order_id'];

            if (isset($json['data']['status']) && isset($status_mapping[$json['data']['status']])) {
                $responseData = $orderService->updateOrderStatusPresta($orderId, $status_mapping[$json['data']['status']], $json);
                $this->logger->info(
                    sprintf(
                        'New notification order.update.status [%s]',
                        $responseData
                    )
                );

                header("HTTP/1.1 200 OK");
                header('Content-Type: application/json');
                echo $responseData;
            } else {
                header("HTTP/1.1 400 Bad Request");
                header('Content-Type: application/json');
                die(json_encode(array(
                    'code' => 'unknown-status',
                    'message' => 'Status is unknnown',
                )));
            }
        }
    }
}
