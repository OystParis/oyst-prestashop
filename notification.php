<?php

use Oyst\Controller\OystOrderController;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

$request = new \Oyst\Service\Http\CurrentRequest();
$data = $request->getJson();

if ($data && isset($data['event'])) {
    switch ($data['event']) {
        case 'order.new':
            $orderController = new OystOrderController($request);
            $orderController->createNewOrderAction();

            break;
        default:
            http_response_code(400);
    }
}
