<?php

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

// To force having a object at the end
$response = new stdClass();

// Read json request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['event'])) {
    switch ($data['event']) {
        case 'catalog.import':
            $oyst = new Oyst();
            $context = Context::getContext();
            Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $productRepository = new ProductRepository(Db::getInstance());
            $exportProductService = new ExportProductService(
                $context,
                $oyst
            );
            $exportProductService->setRepository($productRepository);

            /** @var OystCatalogAPI $oystCatalogAPI */
            $oystCatalogAPI = OystApiClientFactory::getClient(
                OystApiClientFactory::ENTITY_CATALOG,
                $oyst->getApiKey(),
                'PrestaShop-'.$oyst->version,
                $oyst->getEnvironment()
            );

            // Let's go !
            $exportProductController = new ExportProductController($exportProductService, $oystCatalogAPI);
            $exportProductController->setRequestData($data);
            $response = $exportProductController->run();

            break;
        default:
            $response = [
                'totalCount' => 0,
                'remaining' => 0,
                'state' => false,
                'error' => 'Nothing happens'
            ];
    }
}

header('Content-Type: application/json');
echo Tools::jsonEncode($response, JSON_FORCE_OBJECT);
