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

use Oyst\Factory\AbstractOrderServiceFactory;

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystOneclickreturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;

    public function initContent()
    {
        parent::initContent();

        // Get parameters
        $id_cart = (int)$this->context->cookie->oyst_id_cart;
        $key = $this->context->cookie->oyst_key;

        if ($id_cart == 0 && Configuration::get('FC_OYST_HASH_KEY') != $key) {
            Tools::redirect('/');
        }

        $oyst = new Oyst();
        $context = Context::getContext();
        $orderService = AbstractOrderServiceFactory::get($oyst, $context);
        $cartIsError = $orderService->getOrderRepository()->isErrorExist($id_cart);

        // Get cart
        $cart = new Cart($id_cart);
        $customer = new Customer($cart->id_customer);

        if ($this->context->cookie->count > 0) {
            $this->context->cookie->count += 1;
        } else {
            $this->context->cookie->count = 1;
        }

        if ($this->context->cookie->count > 10) {
            unset($this->context->cookie->count);
            $cartIsError = true;
        }

        if ($cartIsError) {
            $url = $this->context->link->getModuleLink('oyst', 'oneclickerror');
            Tools::redirect($url);
        }

        if (isset($this->context->cookie->id_compare)) {
            $id_compare = $this->context->cookie->id_compare;
        } else {
            $id_compare = CompareProduct::getIdCompareByIdCustomer($customer->id);
        }

        // Log user
        if (!$this->context->customer->isLogged()) {
            $this->context->cookie->id_compare = $id_compare;
            $this->context->cookie->id_customer = (int)($customer->id);
            $this->context->cookie->customer_lastname = $customer->lastname;
            $this->context->cookie->customer_firstname = $customer->firstname;
            $this->context->cookie->logged = 1;
            $customer->logged = 1;
            $this->context->cookie->is_guest = $customer->isGuest();
            $this->context->cookie->passwd = $customer->passwd;
            $this->context->cookie->email = $customer->email;
            // Add customer to the context
            $this->context->customer = $customer;
            $this->context->cookie->write();
        }

        // Load cart and order
        $id_order = Order::getOrderByCartId($id_cart);

        if ($id_order) {
            $order = new Order($id_order);
            // If order exists we redirect to confirmation page
            if (Validate::isLoadedObject($order)) {
                // Build urls and amount
                $glue = '&';
                if (Configuration::get('PS_REWRITING_SETTINGS') == 1) {
                    $glue = '?';
                }

                switch (Configuration::get('FC_OYST_OC_REDIRECT_CONF')) {
                    case 'ORDER_HISTORY':
                        $url = $this->context->link->getPageLink('history');
                        break;
                    case 'ORDER_CONFIRMATION':
                        $base_url_confirmation = $this->context->link->getPageLink('order-confirmation');
                        $id_module = Module::getModuleIdByName('oyst');
                        $params = $glue.'id_order='.$id_order.'&id_module='.$id_module.'&id_cart='.$cart->id;
                        $url = $base_url_confirmation.$params.'&key='.$customer->secure_key;
                        break;
                    case 'CUSTOM':
                        $url = Configuration::get('FC_OYST_OC_REDIRECT_CONF_CUSTOM');
                        break;
                }

                Tools::redirect($url);
            }

            // If cart in context is the cart we just paid, we create new cart
            if ($this->context->cart->id == $cart->id) {
                $this->context->cart = new Cart();
                $this->context->cookie->id_cart = 0;
            }
        }

        $this->setTemplate('oneclick-return'.(version_compare(_PS_VERSION_, '1.6.0') ? '.bootstrap' : '').'.tpl');
    }
}
