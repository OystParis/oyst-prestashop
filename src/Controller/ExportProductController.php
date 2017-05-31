<?php

namespace Oyst\Controller;

use Oyst;
use Context;
use Currency;
use Configuration;
use Oyst\Factory\AbstractExportProductServiceFactory;

class ExportProductController extends AbstractOystController
{
    public function exportCatalogAction()
    {
        Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $data = $this->request->getJson();
        $exportProductService = AbstractExportProductServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $exportProductService->sendNewProducts($data['data']['import_id']);
        
        header('Content-Type: application/json');
        echo json_encode($responseData);
    }
}
