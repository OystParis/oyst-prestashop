<?php

namespace Oyst\Services;

use Configuration;
use Currency;
use Order;
use Tools;

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
                    'amount' => $order->total_paid_tax_incl,
                    'paymentMethod' => $order->module,
                    'currency' => $currency->iso_code,
                    'referrer' => urlencode($this->getCurrentUrl()),
                    'merchantId' => Configuration::get('OYST_MERCHANT_ID'),
                ];
            }
        }
        return $parameters;
    }

    private function getCurrentUrl()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}
