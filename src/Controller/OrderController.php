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
use Oyst\Classes\Enum\AbstractOrderState;
use Oyst\Factory\AbstractOrderServiceFactory;

class OrderController extends AbstractOystController
{
    public function createNewOrderAction()
    {
        $json = $this->request->getJson();

        if ($json) {
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderService = AbstractOrderServiceFactory::get($oyst, $context);
            $orderId = $json['data']['order_id'];

            $responseData = $orderService->requestCreateNewOrder($orderId);

            if ($responseData['state']) {
                $orderService->updateOrderStatus($orderId, AbstractOrderState::ACCEPTED);
            } else {
                $orderService->updateOrderStatus($orderId, AbstractOrderState::DENIED);
                $this->logger->critical(sprintf("Error creating order: [%s]", json_encode($responseData['error'])));
            }

            $this->respondAsJson($responseData);
        } else {
            header("HTTP/1.1 400 Bad Request");
        }
    }

    public function updateOrderStatus() {

        $status_mapping = array(
            'suspected_fraud' => 'OYST_STATUS_FRAUD',
        );

        $json = $this->request->getJson();

        if ($json) {
            $oyst = new Oyst();
            $context = Context::getContext();
            $orderService = AbstractOrderServiceFactory::get($oyst, $context);
            $orderId = $json['data']['order_id'];

            if (isset($json['data']['status']) && isset($status_mapping[$json['data']['status']])) {
                $responseData = $orderService->updateOrderStatusPresta($orderId, $json['data']['status'], $json);
                $this->logger->info(
                    sprintf(
                        'New notification order.update.status [%s]',
                        $responseData
                    )
                );

                header("HTTP/1.1 200 OK");
                header('Content-Type: application/json');
                echo $responseData;
            } else {
                header("HTTP/1.1 400 Bad Request");
                header('Content-Type: application/json');
                die(json_encode(array(
                    'code' => 'unknown-status',
                    'message' => 'Status is unknnown',
                )));
            }
        }
    }
}
