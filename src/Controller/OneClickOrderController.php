<?php

namespace Oyst\Controller;

use Oyst;
use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOneClickApi;
use Oyst\Service\OneClickService;
use Context;

class OneClickOrderController extends AbstractOystController
{
    public function authorizeOrderAction()
    {
        $oyst = new Oyst();
        /** @var OystOneClickApi $oneClickAPI */
        $oneClickAPI = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_ONECLICK,
            $oyst->getApiKey(),
            $oyst->getUserAgent(),
            $oyst->getEnvironment()
        );

        $oneClickService = new OneClickService(Context::getContext(), $oyst);
        $oneClickService
            ->setLogger($this->logger)
            ->setOneClickApi($oneClickAPI)
        ;

        if ($this->request->getMethod() === 'POST') {
            $responseData = $oneClickService->requestAuthorizeNewOrderProcess($this->request);

            header('Content-Type: application/json');
            echo json_encode($responseData);
        } else {
            http_response_code(400);
        }
    }
}
