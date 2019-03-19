<?php

namespace Oyst\Classes;

use Cart;
use Message;

class CheckoutBuilder extends AbstractBuilder
{
    public function buildCheckout($id_oyst, $ip, $cart, $customer, $gender_name, $cart_products, $cart_rules, $context, $carriers, $selected_carrier, $address_delivery, $address_invoice, $shop, $currency)
    {
        $response = array();
        $response['id_oyst'] = $id_oyst;
        $response['internal_id'] = $cart->id;
        $response['ip'] = $ip;

        if (!empty($customer)) {
            $response['user'] = $this->getUser($customer, $gender_name, $address_invoice);
        } else {
            $response['user'] = array();
        }

        $response['items'] = $this->getItems($cart_products);

        $response['discounts'] = $this->getDiscounts($cart_rules, $context);
        $response['coupons'] = $this->getCoupons($cart_rules, $context);

        //TODO loyalty points
        $response['user_advantages'] = array(
            'points_fidelity' => array(),
            'balance' => array(),
        );

        $response['shipping']['address'] = $this->formatAddress($address_delivery);
        $response['shipping']['methods_available'] = $this->getAvailableCarriers($carriers, $cart);
        $response['shipping']['method_applied'] = $this->getSelectedCarrier($selected_carrier, $cart);

        $response['billing']['address'] = $this->formatAddress($address_invoice);

        $response['shop'] = $this->getShop($shop);

        $response['totals'] = $this->getCartTotals($cart);

        $response['currency'] = $currency->iso_code;

        $response['message'] = $this->getMessage($cart);

        $response['checkout_agreements'] = $this->getCheckoutAgreements();

        $response['context'] = array();

        return $response;
    }

    protected function getCartTotals($cart)
    {
        return array(
            'details_tax_incl' => array(
                'total_items' => $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $cart->getProducts(), $cart->id_carrier, false),
                'total_shipping' => $cart->getOrderTotal(true, Cart::ONLY_SHIPPING, $cart->getProducts(), $cart->id_carrier, false),
                'total_discount' => $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $cart->getProducts(), $cart->id_carrier, false),
                'total' => $cart->getOrderTotal(true, Cart::BOTH, $cart->getProducts(), $cart->id_carrier, false),
            ),
            'details_tax_excl' => array(
                'total_items' => $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $cart->getProducts(), $cart->id_carrier, false),
                'total_shipping' => $cart->getOrderTotal(false, Cart::ONLY_SHIPPING, $cart->getProducts(), $cart->id_carrier, false),
                'total_discount' => $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $cart->getProducts(), $cart->id_carrier, false),
                'total' => $cart->getOrderTotal(false, Cart::BOTH, $cart->getProducts(), $cart->id_carrier, false),
            ),
            //TODO Separate taxes by tax rate
            'taxes' => array(
                array(
                    "label" => "TVA total",
                    "amount" => $cart->getOrderTotal(true, Cart::BOTH)-$cart->getOrderTotal(false, Cart::BOTH, $cart->getProducts(), $cart->id_carrier, false),
                    "rate" => "20"
                ),
            ),
        );
    }

    protected function getMessage($cart)
    {
        $order_message = Message::getMessageByCartId($cart->id);
        return array(
            array(
                'type' => 'gift',
                'content' => $cart->gift_message,
            ),
            array(
                'type' => 'order',
                'content' => $order_message['message'],
            ),
        );
    }

    protected function getCheckoutAgreements()
    {
        return array(
            'acceptance_message' => '',
            'full_agreements' => '',
        );
    }
}
