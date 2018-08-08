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
use Tools;
use Db;
use Oyst\Factory\AbstractCartServiceFactory;

class CartController extends AbstractOystController
{
    public function estimateAction()
    {
        $data = $this->request->getJson();

        $cartService = AbstractCartServiceFactory::get(new Oyst(), Context::getContext());
        $responseData = $cartService->estimate($data['data']);

        $insert   = array(
            'id_order'   => 0,
            'id_cart'    => 0,
            'payment_id' => '',
            'event_code' => pSQL($data['event']),
            'event_data' => pSQL(Tools::jsonEncode($data['data'])),
            'response'   => pSQL($responseData),
            'date_event' => date('Y-m-d H:i:s'),
            'date_add'   => date('Y-m-d H:i:s'),
        );
        Db::getInstance()->insert('oyst_payment_notification', $insert);

        $this->logger->info(
            sprintf(
                'New notification order.cart.estimate [%s]',
                $responseData
            )
        );

        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
        echo $responseData;
    }
}
