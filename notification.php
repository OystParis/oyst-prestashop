<?php

use Oyst\Controller\OystOrderController;
use Oyst\Service\Logger\PrestaShopLogger;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

$request = new \Oyst\Service\Http\CurrentRequest();
$data = $request->getJson();

if ($data && isset($data['event'])) {

    $logger = new PrestaShopLogger();
    $logger->info(
        sprintf('New notification [%s]', Tools::jsonEncode($data)), array(
            'objectType' => 'OystNotification'
        )
    );

    switch ($data['event']) {
        case 'order.new':
            $orderController = new OystOrderController($request);
            $orderController->setLogger($logger);
            $orderController->createNewOrderAction();
            break;
        default:
            http_response_code(400);
    }
}
