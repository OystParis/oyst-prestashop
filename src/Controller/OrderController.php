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
            $orderExist = $orderService->getOrderRepository()->getOrderExist($orderId);

            if ($orderExist == 0) {
                $guid = $orderService->getOrderRepository()->getOrderGUID($orderId);
                $responseData = $orderService->requestCreateNewOrder($orderId);

                if ($responseData['state']) {
                    $orderService->updateOrderStatus($orderId, AbstractOrderState::ACCEPTED);
                } else {
                    $orderService->updateOrderStatus($orderId, AbstractOrderState::DENIED);
                    $this->logger->critical(sprintf("Error creating order: [%s]", json_encode($responseData['error'])));
                }

                $this->respondAsJson($responseData);
            } else {
                $this->logger->critical(sprintf("Error order exist: [%s]", json_encode($json['data'])));
                http_response_code(200);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
        }
    }
}
