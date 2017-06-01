<?php

namespace Oyst\Controller;

use Oyst;
use Context;
use Oyst\Classes\Enum\AbstractOrderState;
use Oyst\Factory\AbstractNewOrderServiceFactory;

class OrderController extends AbstractOystController
{
    public function createNewOrderAction()
    {
        $json = $this->request->getJson();

        if ($json) {
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderService = AbstractNewOrderServiceFactory::get($oyst, $context);
            $orderId = $json['data']['order_id'];
            $responseData = $orderService->requestCreateNewOrder($orderId);

            $state = $responseData['state'];

            if ($state) {
                $orderService->updateOrderStatus($orderId, AbstractOrderState::ACCEPTED);
            } else {
                $data['error'] = 'The order has no been created';
                $orderService->updateOrderStatus($orderId, AbstractOrderState::DECLINED);
            }

            if (isset($responseData['error'])) {
                $this->logger->critical(sprintf("Error creating order: [%s]", json_encode($responseData)));
            }

            $this->respondAsJson($responseData);
        } else {
            http_response_code(400);
        }
    }
}
