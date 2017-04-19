<?php

use Oyst\Controller\OystOrderController;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\OrderRepository;
use Oyst\Service\NewOrderService;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

// To force having a object at the end
$response = new \Symfony\Component\HttpFoundation\JsonResponse();

// Read json request
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$data = array();

if ($request->getContentType() == 'json') {
    $data = json_decode($request->getContent(), true);
}

if (isset($data['event'])) {
    switch ($data['event']) {
        case 'order.new':
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderRepository = new OrderRepository(Db::getInstance());
            $addressRepository = new AddressRepository(Db::getInstance());
            $orderService = new NewOrderService(
                $context,
                $oyst
            );
            $orderService->setOrderRepository($orderRepository);
            $orderService->setAddressRepository($addressRepository);
            /** @var OystOrderApi $orderApi */
            $orderApi = OystApiClientFactory::getClient(
                OystApiClientFactory::ENTITY_ORDER,
                $oyst->getApiKey(),
                $oyst->getUserAgent(),
                $oyst->getEnvironment()
            );

            $orderService->setOrderAPi($orderApi);

            $orderController = new OystOrderController($request);
            $response = $orderController->createNewOrderAction($orderService, $data['order_id']);

            break;
    }
}

$response->send();
