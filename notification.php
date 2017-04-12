<?php

use Oyst\Controller\ExportProductController;
use Oyst\Repository\ProductRepository;
use Oyst\Service\ExportProductService;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

// To force having a object at the end
$response = new stdClass();

// Read json request
$data = json_decode(file_get_contents('php://input'), true);


$response = [
    'state' => false,
    'error' => 'Method not found',
];

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
            $limitedProduct = (int) getenv('OYST_EXPORT_PRODUCT_NUMBER');
            if ($limitedProduct <= 0) {
                $limitedProduct = ExportProductService::EXPORT_REGULAR_NUMBER;
            }

            $exportProductService->setProductRepository($productRepository);
            $exportProductService->setLimitedProduct($limitedProduct);

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
    }
}

header('Content-Type: application/json');
echo Tools::jsonEncode($response, JSON_FORCE_OBJECT);
