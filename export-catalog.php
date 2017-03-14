<?php

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';
require_once __DIR__ . '/external/autoload.php';

// Prepare Product Service
$oyst = new Oyst();
$context = Context::getContext();
Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
$productRepository = new ProductRepository(Db::getInstance(_PS_USE_SQL_SLAVE_));
$exportProductService = new ExportProductService(
    $context,
    $productRepository,
    $oyst
);

// Prepare Api
$conf = new OystProductApiConfigurationLoader(new Yampee_Yaml_Parser());
$conf
    ->load()
    //->setEnvironment(Configuration::get('OYST_ENV'))
;

$oystCatalogAPI = new OystCatalogAPI($conf, Configuration::get('FC_OYST_API_KEY'), 'PrestaShop-'.$oyst->version);

// Let's go !
$exportProductController = new ExportProductController($exportProductService, $oystCatalogAPI);
$exportProductController->run();

// And if really required a method to display a Response properly.
//->sendResponse()
