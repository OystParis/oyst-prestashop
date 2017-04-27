<?php

use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystOneClickApi;
use Oyst\Controller\OneClickOrderController;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\OneClickService;

require_once __DIR__.'/../../config/config.inc.php';
require __DIR__.'/oyst.php';

// TODO: Add a way to secure this call (CSRF Token for example).

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
    ->setLogger(new PrestaShopLogger())
;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$oneClickController = new OneClickOrderController($request);
$oneClickController
    ->authorizeOrderAction($oneClickService)
    ->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK)
    ->send()
;
