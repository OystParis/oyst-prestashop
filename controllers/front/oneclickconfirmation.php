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

class OystOneclickconfirmationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $id_cart;
    public $id_module;
    public $id_order;
    public $reference;
    public $secure_key;
    public $display_column_left = false;
    public $display_column_right = false;

    /**
     * Initialize oneclick order confirmation controller
     * @see FrontController::init()
     */
    public function init()
    {
        parent::init();

        $this->id_cart = (int)$this->context->cookie->oyst_id_cart;

        $redirectLink = 'index.php?controller=history';

        $this->id_module = (int)(Tools::getValue('id_module', 0));
        $this->id_order = Order::getOrderByCartId((int)($this->id_cart));
        $order = new Order((int)($this->id_order));
        if (!$this->id_order || !$this->id_module || !$this->context->customer->id) {
            Tools::redirect($redirectLink.(Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }
        $this->reference = $order->reference;
        if (!Validate::isLoadedObject($order) || $order->id_customer != $this->context->customer->id) {
            Tools::redirect($redirectLink);
        }
        $module = Module::getInstanceById((int)($this->id_module));
        if ($order->module != $module->name) {
            Tools::redirect($redirectLink);
        }
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'id_order' => $this->id_order,
            'reference_order' => $this->reference,
            'id_order_formatted' => sprintf('#%06d', $this->id_order),
        ));

        unset($this->context->cookie->oyst_key);
        unset($this->context->cookie->oyst_id_cart);

        $this->setTemplate('oneclick-confirmation'.(version_compare(_PS_VERSION_, '1.6.0', '>=') ? '.bootstrap' : '').'.tpl');
    }
}
