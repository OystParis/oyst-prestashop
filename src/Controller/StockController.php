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
use Oyst\Factory\AbstractStockServiceFactory;

class StockController extends AbstractOystController
{
    public function stockReleased()
    {
        $data = $this->request->getJson();
        $shipmentService = AbstractStockServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $shipmentService->manageQty($data['data']);
        $this->logger->info(
            sprintf(
                'New notification stock [%s]',
                $this->respondAsJson($data['data'])
            )
        );

        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
    }

    public function stockBook()
    {
        $data = $this->request->getJson();
        $shipmentService = AbstractStockServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $shipmentService->stockBook($data['data']);

        $this->logger->info(
            sprintf(
                'New notification stock [%s]',
                json_encode($responseData)
            )
        );

        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
        echo json_encode($responseData);
    }
}
