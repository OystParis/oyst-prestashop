<?php

use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOneClickApi;
use Oyst\Controller\OneClickOrderController;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Service\OneClickService;

require_once __DIR__.'/../../config/config.inc.php';
require __DIR__.'/oyst.php';

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
    ->setOneClickApi($oneClickAPI)
;

$oneClickController = new OneClickOrderController(new CurrentRequest());
$oneClickController->authorizeOrderAction($oneClickService);
