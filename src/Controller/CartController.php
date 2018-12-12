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
use Tools;
use Db;
use Oyst\Factory\AbstractCartServiceFactory;

class CartController extends AbstractOystController
{
    public function estimateAction()
    {
        $data = $this->request->getJson();

        $context = Context::getContext();
        $cart_service = AbstractCartServiceFactory::get(new Oyst(), $context);
        $response_data = $cart_service->estimate($data['data']);

        // Remove addresses from cart
        $context->cart->id_address_delivery = 0;
        $context->cart->id_address_invoice = 0;
        $context->cart->save();

        $response_data_array = json_decode($response_data, true);
        if (isset($response_data_array['error']) && $response_data_array['error']) {
            $this->respondAsError($response_data);
        }

        $insert   = array(
            'id_order'   => 0,
            'id_cart'    => 0,
            'payment_id' => '',
            'event_code' => pSQL($data['event']),
            'event_data' => pSQL(Tools::jsonEncode($data['data'])),
            'response'   => pSQL($response_data),
            'date_event' => date('Y-m-d H:i:s'),
            'date_add'   => date('Y-m-d H:i:s'),
        );
        Db::getInstance()->insert('oyst_payment_notification', $insert);

        $this->logger->info(
            sprintf(
                'New notification order.cart.estimate [%s]',
                $response_data
            )
        );

        $this->respondAsJson($response_data);
    }
}
