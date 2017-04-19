<?php

namespace Oyst\Controller;

use Oyst\Service\NewOrderService;
use Symfony\Component\HttpFoundation\JsonResponse;

class OystOrderController extends AbstractOystController
{
    /**
     * @param NewOrderService $orderService
     * @param $orderId
     * @return JsonResponse
     */
    public function createNewOrderAction(NewOrderService $orderService, $orderId)
    {
        $response = $orderService->requestCreateNewOrder($orderId);

        return $response;
    }
}
