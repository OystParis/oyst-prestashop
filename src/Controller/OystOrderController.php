<?php

namespace Oyst\Controller;

use Oyst;
use Context;
use Db;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOrderApi;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\OrderRepository;
use Oyst\Service\NewOrderService;

class OystOrderController extends AbstractOystController
{
    public function createNewOrderAction()
    {
        header('Content-Type: application/json');

        $oyst = new Oyst();
        $context = Context::getContext();
        $orderRepository = new OrderRepository(Db::getInstance());
        $addressRepository = new AddressRepository(Db::getInstance());

        /** @var OystOrderApi $orderApi */
        $orderApi = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ORDER,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getApiUrl()
        );

        $orderService = new NewOrderService(
            $context,
            $oyst
        );

        $orderService
            ->setOrderRepository($orderRepository)
            ->setAddressRepository($addressRepository)
            ->setLogger($this->logger)
            ->setOrderAPi($orderApi)
        ;

        $json = $this->request->getJson();
        if ($json) {
            $responseData = $orderService->requestCreateNewOrder($json['data']['order_id']);
            echo json_encode($responseData);
        } else {
            http_response_code(400);
        }
    }
}
