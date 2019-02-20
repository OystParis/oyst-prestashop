<?php

namespace Oyst\Service;

use Configuration as PSConfiguration;
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

    private $order;

    private function __construct() {
        $this->order = $this->getOrder();
    }

    private function __clone() {}

    public function getTrackingHtml($id_order = 0)
    {
        if (!Validate::isLoadedObject($this->order)) {
            if (!empty($id_order)) {
                $this->order = new Order($id_order);
            }

            if (!Validate::isLoadedObject($this->order)) {
                return '';
            }
        }

        return '<img src="'.$this->getTrackerBaseUrl().'?'.$this->getExtraParameters().'"/>';
    }

    protected function getOrder()
    {
        if (Tools::getIsset('id_cart')) {
            $id_cart = (int)Tools::getValue('id_cart');
            $id_order = Order::getOrderByCartId($id_cart);
            return new Order($id_order);
        } elseif (Tools::getIsset('id_order')) {
            return new Order(Tools::getValue('id_order'));
        }
        return null;
    }

    protected function getTrackerBaseUrl()
    {
        if (PSConfiguration::get('OYST_ONE_CLICK_MODE') == 'prod') {
            return 'https://tkr.11rupt.io/';
        } else {
            return 'https://staging-tkr.11rupt.eu/';
        }
    }

    protected function getExtraParameters()
    {
        $currency = new Currency($this->order->id_currency);
        $extra_parameters = array(
            'event=Confirmation%20Displayed',
            'type=track',
            'version=1',
            'extra_parameters[amount]='.$this->order->total_paid_tax_incl,
            'extra_parameters[paymentMethod]='.$this->formatPaymentMethod($this->order->module),
            'extra_parameters[currency]='.$currency->iso_code,
        );
        if (PSConfiguration::hasKey('FC_OYST_MERCHANT_ID')) {
            $extra_parameters[] = 'extra_parameters[merchantId]='.PSConfiguration::get('FC_OYST_MERCHANT_ID');
        }
        return implode('&', $extra_parameters);
    }

    protected function formatPaymentMethod($payment_method)
    {
        return strtolower(str_replace(array('-', ' '), '_', $payment_method));
    }

    public function getCurrentUrl()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}
