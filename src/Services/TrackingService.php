<?php

namespace Oyst\Services;

use Configuration;
use Currency;
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

    public function getTrackingParameters()
    {
        $parameters = [];
        if (Tools::getIsset('id_order')) {
            $order = new Order(Tools::getValue('id_order'));
            if (Validate::isLoadedObject($order)) {
                $currency = new Currency($order->id_currency);
                $parameters = [
                    'event' => 'Confirmation%20Displayed',
                    'type' => 'track',
                    'version' => 1,
                    'amount' => $order->total_paid_tax_incl,
                    'paymentMethod' => $this->formatPaymentMethod($order->module),
                    'currency' => $currency->iso_code,
                    'merchantId' => Configuration::get('OYST_MERCHANT_ID'),
                ];
            }
        }
        return $parameters;
    }

    protected function formatPaymentMethod($payment_method)
    {
        return strtolower(str_replace(array('-', ' '), '_', $payment_method));
    }
}
