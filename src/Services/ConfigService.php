<?php

namespace Oyst\Services;

use Carrier;
use Country;
use Language;
use OrderState;
use Shop;

class ConfigService
{
    private static $instance;

    private $id_lang;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ConfigService();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->id_lang = Language::getIdByIso('FR');
    }
    private function __clone() {}

    public function getCarriers()
    {
        $results = [];

        $carriers = Carrier::getCarriers($this->id_lang, true, false, false, null, Carrier::ALL_CARRIERS);
        foreach ($carriers as $carrier) {
            $results[] = [
                'label' => $carrier['name'],
                'reference' => $carrier['id_reference'],
                'delivery_delay' => $carrier['delay'],
            ];
        }
        return $results;
    }

    public function getCountries()
    {
        $results = [];

        $countries = Country::getCountries($this->id_lang, true);
        foreach ($countries as $country) {
            $results[] = [
                'name' => $country['name'],
                'code' => $country['iso_code'],
            ];
        }
        return $results;
    }

    public function getOrderStatuses()
    {
        $results = [];

        $order_states = OrderState::getOrderStates($this->id_lang);
        foreach ($order_states as $order_state) {
            $results[] = [
                'label' => $order_state['name'],
                'code' => $order_state['id_order_state'],
            ];
        }
        return $results;
    }

    public function getShops()
    {
        $results = [];

        $shops = Shop::getShops(false);
        foreach ($shops as $shop) {
            $shop_obj = new Shop($shop['id_shop']);
            $results[] = [
                'url' => $shop_obj->getBaseURL(true),
                'code' => $shop_obj->id,
                'label' => $shop_obj->name,
            ];
        }
        return $results;
    }
}
