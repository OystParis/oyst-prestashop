<?php

namespace Oyst\Controller;

use Oyst;
use Context;
use Currency;
use Configuration;
use Oyst\Factory\ExportProductServiceFactory;

class ExportProductController extends AbstractOystController
{
    public function exportCatalogAction()
    {
        header('Content-Type: application/json');

        Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $data = $this->request->getJson();
        $exportProductService = ExportProductServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $exportProductService->sendNewProducts($data['data']['import_id']);

        echo json_encode($responseData);
    }
}
