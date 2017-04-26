<?php

use Oyst\Api\OystApiClientFactory;
use Oyst\Api\OystCatalogApi;
use Oyst\Api\OystOrderApi;
use Oyst\Controller\ExportProductController;
use Oyst\Repository\ProductRepository;
use Oyst\Service\ExportProductService;
use Oyst\Controller\OystOrderController;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\OrderRepository;
use Oyst\Service\Logger\PrestaShopLogger;
use Oyst\Service\NewOrderService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../config/config.inc.php';
require_once __DIR__.'/oyst.php';

// To force having a object at the end
$response = new JsonResponse();

// Read json request
$request = Request::createFromGlobals();

$data = array(
    'state' => false,
    'error' => 'Method not found',
);

if ($request->getContentType() == 'json') {
    $data = json_decode($request->getContent(), true);
}

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

            $exportProductService
                ->setProductRepository($productRepository)
                ->setLimitedProduct($limitedProduct)
                ->setLogger(new PrestaShopLogger())
            ;

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
        case 'order.new':
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderRepository = new OrderRepository(Db::getInstance());
            $addressRepository = new AddressRepository(Db::getInstance());
            $orderService = new NewOrderService(
                $context,
                $oyst
            );
            $orderService
                ->setOrderRepository($orderRepository)
                ->setAddressRepository($addressRepository)
                ->setLogger(new PrestaShopLogger())
            ;
            /** @var OystOrderApi $orderApi */
            $orderApi = OystApiClientFactory::getClient(
                OystApiClientFactory::ENTITY_ORDER,
                $oyst->getApiKey(),
                $oyst->getUserAgent(),
                $oyst->getEnvironment()
            );

            $orderService->setOrderAPi($orderApi);

            $orderController = new OystOrderController($request);
            $response = $orderController->createNewOrderAction($orderService, $data['order_id']);

            break;
    }
}

$response->send();
