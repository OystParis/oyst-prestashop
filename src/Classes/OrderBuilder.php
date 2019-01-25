<?php

namespace Oyst\Classes;

use Cart;
use Message;
use OrderState;

class OrderBuilder extends AbstractBuilder
{
    public function buildOrder($id_oyst, $ip, $order, $order_state, $customer, $gender_name, $order_details, $cart_rules, $context, $selected_carrier, $address_delivery, $address_invoice, $shop, $currency)
    {
        $response = array();
        $response['id_oyst'] = $id_oyst;
        $response['internal_id'] = $order->id;
        $response['created_at'] = $order->date_add;
        $response['updated_at'] = $order->date_upd;
        $response['status'] = array(
            'code' => $order_state->id,
            'label' => $order_state->name[$this->id_lang],
        );
        $response['ip'] = $ip;

        if (!empty($customer)) {
            $response['user'] = $this->getUser($customer, $gender_name, $address_invoice);
        } else {
            $response['user'] = array();
        }

//        $response['items'] = $this->getItems($order_details);

        $response['discounts'] = array_merge($this->getDiscounts($cart_rules, $context), $this->getCoupons($cart_rules, $context));
        $response['coupons'] = array();
//        $response['coupons'] = $this->getCoupons($cart_rules, $context);

        //TODO loyalty points
        $response['user_advantages'] = array(
            'points_fidelity' => array(),
            'balance' => array(),
        );

        $response['shipping']['address'] = $this->formatAddress($address_delivery);
        $response['shipping']['method_applied'] = $this->getOrderCarrier($selected_carrier, $order);

        $response['billing']['address'] = $this->formatAddress($address_invoice);

        $response['shop'] = $this->getShop($shop);

        $response['totals'] = $this->getOrderTotals($order);

        $response['currency'] = $currency->iso_code;

//        $response['message'] = $this->getMessage($cart);

        $response['context'] = array();

        return $response;
    }

    protected function getOrderTotals($order)
    {
        return array(
            'details_tax_incl' => array(
                'total_items' => $order->total_products_wt,
                'total_shipping' => $order->total_shipping_tax_incl,
                'total_discount' => $order->total_discounts_tax_incl,
                'total' => $order->total_paid_tax_incl,
            ),
            'details_tax_excl' => array(
                'total_items' => $order->total_products,
                'total_shipping' => $order->total_shipping_tax_excl,
                'total_discount' => $order->total_discounts_tax_excl,
                'total' => $order->total_paid_tax_excl,
            ),
            //TODO Separate taxes by tax rate
            'taxes' => array(
                array(
                    "label" => "TVA total",
                    "amount" => $order->total_paid_tax_incl-$order->total_paid_tax_excl,
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

    protected function getOrderCarrier($carrier, $order)
    {
        if (!empty($carrier)) {
            return array(
                'label' => $carrier->name,
                'reference' => $carrier->id_reference,
                'delivery_delay' => '48', //Temp fix value, 48h TODO
                'amount_tax_incl' => $order->total_shipping_tax_incl,
                'amount_tax_excl' => $order->total_shipping_tax_excl,
            );
        }
    }
}
