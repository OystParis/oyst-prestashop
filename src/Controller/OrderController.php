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

            $state = $responseData['state'];

            if ($state) {
                $orderService->updateOrderStatus($orderId, AbstractOrderState::ACCEPTED);
            } else {
                $responseData['error'] = 'The order has no been created';
                $orderService->updateOrderStatus($orderId, AbstractOrderState::DECLINED);
            }

            if (isset($responseData['error'])) {
                $this->logger->critical(sprintf("Error creating order: [%s]", json_encode($responseData)));
            }

            $this->respondAsJson($responseData);
        } else {
            http_response_code(400);
        }
    }
}
