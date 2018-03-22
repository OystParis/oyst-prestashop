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

class OystPaymentreturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        // Get parameters
        $id_cart = (int)Tools::getValue('id_cart');
        $key = Tools::getValue('key');

        // Get cart
        $cart = new Cart($id_cart);
        $customer = new Customer($cart->id_customer);
        if ($customer->secure_key != $key) {
            die('Wrong security key');
        }

        // Load cart and order
        $id_order = Order::getOrderByCartId($id_cart);
        $order = new Order($id_order);

        // If order exists we redirect to confirmation page
        if (Validate::isLoadedObject($order)) {
            // Build urls and amount
            $glue = '&';
            if (Configuration::get('PS_REWRITING_SETTINGS') == 1) {
                $glue = '?';
            }

            $url = '';

            switch (Configuration::get('FC_OYST_REDIRECT_SUCCESS')) {
                case 'ORDER_HISTORY':
                    $url = $this->context->link->getPageLink('history');
                    break;
                case 'ORDER_CONFIRMATION':
                    $url_confirmation = $this->context->link->getPageLink('order-confirmation');
                    $id_module = Module::getModuleIdByName('oyst');
                    $params = $glue.'id_cart='.$cart->id.'&id_module='.$id_module.'&key='.$customer->secure_key;
                    $url = $url_confirmation.$params;
                    break;
                case 'CUSTOM':
                    $url = Configuration::get('FC_OYST_REDIRECT_SUCCESS_CUSTOM');
                    break;
            }

            Tools::redirect($url);
        }

        // If cart in context is the cart we just paid, we create new cart
        if ($this->context->cart->id == $cart->id) {
            $this->context->cart = new Cart();
            $this->context->cookie->id_cart = 0;
        }

        $this->setTemplate('return'.(version_compare(_PS_VERSION_, '1.6.0') ? '.bootstrap' : '').'.tpl');
    }
}
