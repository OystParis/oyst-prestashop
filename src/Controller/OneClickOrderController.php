<?php

namespace Oyst\Controller;

use Oyst\Service\OneClickService;
use Symfony\Component\HttpFoundation\JsonResponse;

class OneClickOrderController extends AbstractOystController
{
    /**
     * @param OneClickService $oneClickService
     * @return JsonResponse
     */
    public function authorizeOrderAction(OneClickService $oneClickService)
    {
        $response = $oneClickService->requestAuthorizeNewOrderProcess($this->request);

        return $response;
    }
}
