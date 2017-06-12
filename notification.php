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

use Oyst\Controller\ExportProductController;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Controller\OrderController;
use Oyst\Service\Logger\PrestaShopLogger;

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'/oyst.php';

$request = new CurrentRequest();
$data = $request->getJson();

$logger = new PrestaShopLogger();

if ($data && isset($data['event'])) {
    $logger->info(
        sprintf(
            'New notification [%s]',
            Tools::jsonEncode($data)
        ),
        array('objectType' => 'OystNotification')
    );

    switch ($data['event']) {
        case 'catalog.import':
            $exportProductController = new ExportProductController($request);
            $exportProductController->setLogger($logger);
            $exportProductController->exportCatalogAction();
            break;
        case 'order.new':
            $orderController = new OrderController($request);
            $orderController->setLogger($logger);
            $orderController->createNewOrderAction();
            break;
        default:
            http_response_code(400);
    }
} else {
    $logger->warning(
        sprintf(
            '[Bad request notification] Request[%s] RawBody:[%s] Body[%s] GET[%s] POST[%s]',
            json_encode($_REQUEST),
            $request->getBody(),
            json_encode($data),
            json_encode($request->getQuery()),
            json_encode($request->getRequest())
        ),
        array(
            'objectType' => 'OystNotification'
        )
    );
    http_response_code(400);
}
