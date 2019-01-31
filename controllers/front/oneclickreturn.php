<?php

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystOneclickreturnModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (!empty($this->context->cookie->oyst_id_cart)) {
            $cart = new Cart($this->context->cookie->oyst_id_cart);
            if (Validate::isLoadedObject($cart)) {
                $customer = new Customer($cart->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    //Connect the customer if not already connected
                    if (!$this->context->customer->isLogged()) {
                        $this->context->cookie->id_compare = 0;
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
                    }
                    unset($this->context->cookie->oyst_id_cart);
                    $this->context->cookie->write();
                    $id_order = Order::getOrderByCartId($cart->id);
                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$id_order.'&key='.$customer->secure_key);
                }
            }
        }
        Tools::redirect('/');
    }
}
