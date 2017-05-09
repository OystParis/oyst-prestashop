<?php

namespace Oyst\Controller;

use Oyst\Service\OneClickService;

class OneClickOrderController extends AbstractOystController
{
    /**
     * @param OneClickService $oneClickService
     */
    public function authorizeOrderAction(OneClickService $oneClickService)
    {
        header('Content-Type: application/json');
        if ($this->request->getMethod() === 'POST') {
            $responseData = $oneClickService->requestAuthorizeNewOrderProcess($this->request);
            echo json_encode($responseData);
        } else {
            http_response_code(400);
        }
    }
}
