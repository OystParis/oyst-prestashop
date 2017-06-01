<?php

use Oyst\Controller\ExportProductController;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Controller\OrderController;
use Oyst\Service\Logger\PrestaShopLogger;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

$request = new CurrentRequest();
$data = $request->getJson();

$logger = new PrestaShopLogger();
$logger->info(
    sprintf('New notification request Request[%s] RawBody:[%s] Body[%s] GET[%s] POST[%s]',
        json_encode($_REQUEST),
        $request->getBody(),
        json_encode($data),
        json_encode($request->getQuery()),
        json_encode($request->getRequest())
    ), array(
        'objectType' => 'OystNotification'
    )
);

if ($data && isset($data['event'])) {

    $logger->info(
        sprintf('New notification [%s]', Tools::jsonEncode($data)), array(
            'objectType' => 'OystNotification'
        )
    );

    switch ($data['event']) {
        case 'catalog.import':
            $exportProductController = new ExportProductController($request);
            $exportProductController->setLogger($logger);
            $exportProductController->exportCatalogAction();
            break;
        case 'order.new':
            $orderController = new OrderController($request);
            $orderController->setLogger($logger);
            $orderController->createNewOrderAction();
            break;
        default:
            http_response_code(400);
    }
} else {
    http_response_code(400);
}
