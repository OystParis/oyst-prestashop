<?php

namespace Oyst\Services;

use Configuration;
use Currency;
use Customer;
use Order;
use Tools;
use Validate;

class TrackingService
{
    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new TrackingService();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}

    public function getTrackingParameters($json = false)
    {
        $results = [];
        if (Tools::getIsset('id_order')) {
            $order = new Order(Tools::getValue('id_order'));
            if (Validate::isLoadedObject($order)) {
                $currency = new Currency($order->id_currency);
                $results['parameters'] = [
                    'event' => 'Confirmation Displayed',
                    'type' => 'track',
                    'version' => 1,
                ];

                $customer = new Customer($order->id_customer);

                $results['extraParameters'] = [
                    'amount' => $order->total_paid_tax_incl,
                    'paymentMethod' => $this->formatPaymentMethod($order->module),
                    'currency' => $currency->iso_code,
                    'merchantId' => Configuration::get('OYST_MERCHANT_ID'),
                    'userEmail' => $customer->email,
                    'orderId' => $order->id,
                    'userId' => $customer->id,
                ];
            }
        }
        if ($json) {
            return json_encode($results);
        } else {
            return $results;
        }
    }

    protected function formatPaymentMethod($payment_method)
    {
        return strtolower(str_replace(array('-', ' '), '_', $payment_method));
    }
}
