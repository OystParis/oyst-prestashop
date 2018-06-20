<?php
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

        $this->id_cart = (int)(Tools::getValue('id_cart', 0));

        $redirectLink = 'index.php?controller=history';

        $this->id_module = (int)(Tools::getValue('id_module', 0));
        if (version_compare(_PS_VERSION_, '1.7.1', '<')) {
            $this->id_order = Order::getOrderByCartId($this->id_cart);
        } else {
            $this->id_order = Order::getByCartId($this->id_cart);
        }

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

        $this->setTemplate('oneclick-confirmation'.(version_compare(_PS_VERSION_, '1.6.0', '>=') ? '.bootstrap' : '').'.tpl');
    }
}
