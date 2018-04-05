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

use Oyst\Controller\ExportProductController;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Controller\OrderController;
use Oyst\Controller\ShipmentController;
use Oyst\Controller\StockController;
use Oyst\Controller\CartController;
use Oyst\Factory\AbstractOrderServiceFactory;

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'/oyst.php';

$request = new CurrentRequest();
$data = $request->getJson();

$logger = new \Oyst\Service\Logger\FileLogger();
$logger->setFile(dirname(__FILE__).'/logs/notification.log');

if (Tools::getValue('key') != Configuration::get('FC_OYST_HASH_KEY')) {
    $logger->info('New notification : Secure key is invalid');
    header("HTTP/1.1 400 Bad Request");
}

if ($data && isset($data['event'])) {
    $logger->info(
        sprintf(
            'New notification [%s]',
            Tools::jsonEncode($data)
        ),
        array('objectType' => 'OystNotification')
    );

    try {
        switch ($data['event']) {
            case 'order.new':
            case 'order.v2.new':
                $oyst = new Oyst();
                $context = Context::getContext();
                $orderService = AbstractOrderServiceFactory::get($oyst, $context);
                $orderController = new OrderController($request);
                $orderGUID = $data['data']['order_id'];
                $orderAlreadyBeenTreated = $orderService->getOrderRepository()->isOrderAlreadyBeenTreated($orderGUID);
                $forceResendOrder = (isset($data['notification']) && $data['notification']);

                if (!$orderAlreadyBeenTreated || $forceResendOrder) {
                    $insert   = array(
                        'id_order'   => 0,
                        'id_cart'    => 0,
                        'payment_id' => pSQL($orderGUID),
                        'event_code' => pSQL($data['event']),
                        'event_data' => '',
                        'status'     => 'start',
                        'date_add'   => date('Y-m-d H:i:s'),
                        'date_upd'   => date('Y-m-d H:i:s'),
                    );
                    Db::getInstance()->insert('oyst_payment_notification', $insert);

                    $orderController->setLogger($logger);
                    $orderController->createNewOrderAction();
                } else {
                    $orderId = $orderService->getOrderRepository()->getOrderId($orderGUID);
                    $order = new Order($orderId);
                    $response = json_encode(
                        array(
                            "prestashop_order_id" => ".$order->id.",
                            "message" => "notification has been already processed."
                        )
                    );

                    $insert   = array(
                        'id_order'   => (int)$order->id,
                        'id_cart'    => (int)$order->id_cart,
                        'payment_id' => pSQL($orderGUID),
                        'event_code' => pSQL($data['event']),
                        'event_data' => $response,
                        'status'     => 'finished',
                        'date_event' => date('Y-m-d H:i:s'),
                        'date_add'   => date('Y-m-d H:i:s'),
                        'date_upd'   => date('Y-m-d H:i:s'),
                    );
                    Db::getInstance()->insert('oyst_payment_notification', $insert);
                    $logger->critical(sprintf("Error order exist: [%s]", json_encode($data['data'])));
                    header("HTTP/1.1 200 OK");
                    echo $response;
                }
                break;
            case 'order.stock.released':
                $stockController = new StockController($request);
                $stockController->setLogger($logger);
                $stockController->stockReleased();
                break;
            case 'order.stock.book':
                $stockController = new StockController($request);
                $stockController->setLogger($logger);
                $stockController->stockBook();
                break;
            case 'order.cart.estimate':
                $cartController = new CartController($request);
                $cartController->setLogger($logger);
                $cartController->estimateAction();
                break;
            default:
                header("HTTP/1.1 400 Bad Request");
        }
    } catch (Exception $exception) {
        $logger->critical($exception->getMessage());
        header("HTTP/1.1 500 Internal Server Error");
        header('Content-Type: application/json');
        echo json_encode(array('critical' => $exception->getMessage()));
    }
} else {
    $logger->warning(
        sprintf(
            '[Bad request notification] Request[%s] RawBody:[%s] Body[%s] GET[%s] POST[%s]',
            json_encode($_REQUEST),
            $request->getBody(),
            json_encode($data),
            json_encode($request->getQuery()),
            json_encode($request->getRequest())
        ),
        array(
            'objectType' => 'OystNotification'
        )
    );
    header("HTTP/1.1 400 Bad Request");
}
