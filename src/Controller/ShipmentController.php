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
use Oyst\Factory\AbstractShipmentServiceFactory;

class ShipmentController extends AbstractOystController
{
    public function getShipmentsAction()
    {
        $data = $this->request->getJson();
        $shipmentService = AbstractShipmentServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $shipmentService->getShipments($data['data']);
        $this->logger->info(
            sprintf(
                'New notification shipments [%s]',
                $responseData
            )
        );

        header('Content-Type: application/json');
        echo $responseData;
        http_response_code(200);
    }
}
