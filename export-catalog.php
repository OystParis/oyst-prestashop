<?php

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

$context = Context::getContext();
Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

// Prepare Product Service
$oyst = new Oyst();

// Get Api client
$oystCatalogAPI = OystApiClientFactory::getClient(
    OystApiClientFactory::ENTITY_CATALOG,
    $oyst->getApiKey(),
    'PrestaShop-'.$oyst->version,
    $oyst->getEnvironment()
);

// Get PrestaShop requirement for dependencies
$productRepository = new ProductRepository(Db::getInstance());
$exportProductService = new ExportProductService(
    $context,
    $oyst
);
$exportProductService->setRepository($productRepository);

// Let's go !
$exportProductController = new ExportProductController($exportProductService, $oystCatalogAPI);
$result = $exportProductController->run();

dump($result);

// And if really required a method to display a Response properly.
//->sendResponse()
