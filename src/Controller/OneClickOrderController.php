<?php

namespace Oyst\Controller;

use Oyst;
use Oyst\Factory\AbstractOneClickServiceFactory;
use Context;

class OneClickOrderController extends AbstractOystController
{
    public function authorizeOrderAction()
    {
        $oyst = new Oyst();

        $oneClickService = AbstractOneClickServiceFactory::get($oyst, Context::getContext());

        if ($this->request->getMethod() === 'POST') {
            $responseData = $oneClickService->requestAuthorizeNewOrderProcess($this->request);

            header('Content-Type: application/json');
            echo json_encode($responseData);
        } else {
            http_response_code(400);
        }
    }
}
