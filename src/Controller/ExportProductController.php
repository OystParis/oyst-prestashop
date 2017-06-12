<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

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
