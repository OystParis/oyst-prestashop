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
                $orderId = $data['data']['order_id'];
                $orderExist = $orderService->getOrderRepository()->getOrderExist($orderId);

                if ($orderExist == 0) {
                    $orderController->setLogger($logger);
                    $orderController->createNewOrderAction();
                } else {
                    $this->logger->critical(sprintf("Error order exist: [%s]", json_encode($json['data'])));
                    header("HTTP/1.1 200 OK");
                }
                break;
            case 'order.shipments.get':
                $shipmentController = new ShipmentController($request);
                $shipmentController->setLogger($logger);
                $shipmentController->getShipmentsAction();
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
