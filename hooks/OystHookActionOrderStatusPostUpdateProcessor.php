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

/*
 * Security
 */
use Oyst\Classes\Enum\AbstractOrderState;
use Oyst\Factory\AbstractOrderServiceFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookActionOrderStatusPostUpdateProcessor extends FroggyHookProcessor
{
    public function run()
    {
        /** @var OrderState $orderSate */
        $orderSate = $this->params['newOrderStatus'];

        // As the order is by default accepted, we ask for a refund when canceled
        if ($orderSate->id == 7) {
            $orderService = AbstractOrderServiceFactory::get(
                $this->module,
                $this->context
            );

            $guid = $orderService->getOrderRepository()->getOrderGUID($this->params['id_order']);
            $orderService->updateOrderStatus($guid, AbstractOrderState::REFUNDED);
        }
    }
}
