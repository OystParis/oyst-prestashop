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
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayOrderConfirmationProcessor extends FroggyHookProcessor
{
    public function run()
    {
        if (Tools::getIsset('id_order')) {
            $order = new Order(Tools::getValue('id_order'));

            $this->context->smarty->assign(array(
                'id_order' => $order->id,
                'reference_order' => $order->reference,
                'id_order_formatted' => sprintf('#%06d', $order->id),
            ));
        }

        unset($this->context->cookie->oyst_key);
        unset($this->context->cookie->oyst_id_cart);

        return $this->module->fcdisplay(__FILE__, 'OrderConfirmation.tpl');
    }
}
