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

class OystPaymenterrorModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();
        // Deprecated for version 1.11.0
        // if (Configuration::get('FC_OYST_PREORDER_FEATURE')) {
        //     if (Tools::getIsset('id_cart')) {
        //         $id_cart = Tools::getValue('id_cart');
        //         $id_order = Order::getOrderByCartId($id_cart);
        //
        //         $oyst = new Oyst();
        //         $context = Context::getContext();
        //         $orderService = AbstractOrderServiceFactory::get(
        //             $oyst,
        //             $context
        //         );
        //         $orderService->updateOrderStatus($id_order, AbstractOrderState::DENIED);
        //         $this->updateOrderStatus($id_cart, Configuration::get('PS_OS_CANCELED'));
        //     }
        // }
        if (_PS_OYST_DEBUG_ == 1 && Tools::getValue('debug') == Configuration::get('FC_OYST_HASH_KEY')) {
            $function = 'base64'.'_'.'decode';
            $this->context->smarty->assign('oyst_debug', Tools::jsonDecode($function($this->context->cookie->oyst_debug), true));
        }
        $this->setTemplate('error'.(version_compare(_PS_VERSION_, '1.6.0') ? '.bootstrap' : '').'.tpl');
    }

    public function updateOrderStatus($id_cart, $id_order_state)
    {
        // Get order ID
        $id_order = Order::getOrderByCartId($id_cart);
        if ($id_order > 0 && $id_order_state > 0) {
            // Create new OrderHistory
            $history = new OrderHistory();
            $history->id_order = $id_order;
            $history->id_employee = 0;
            $history->id_order_state = (int)$id_order_state;
            $history->changeIdOrderState((int)$id_order_state, $id_order);
            $history->add();
        }
    }
}
