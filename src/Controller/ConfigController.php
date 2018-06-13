<?php

namespace Oyst\Controller;

use Carrier;
use Country;
use Language;
use OrderState;

class ConfigController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('config');
    }

    public function getConfig()
    {
        $results = array();

        $id_lang = Language::getIdByIso('FR');
        //Get carriers
        $carriers = Carrier::getCarriers($id_lang, true, false, false, null, Carrier::ALL_CARRIERS);
        foreach ($carriers as $carrier) {
            $results['carriers'][] = array(
                'id_carrier_reference' => $carrier['id_reference'],
                'name' => $carrier['name'],
            );
        }

        //Get countries
        $countries = Country::getCountries($id_lang, true);
        foreach ($countries as $country) {
            $results['countries'][] = array(
                'id_country' => $country['id_country'],
                'name' => $country['name'],
                'iso_code' => $country['iso_code'],
            );
        }

        //Get status
        $order_states = OrderState::getOrderStates($id_lang);
        foreach ($order_states as $order_state) {
            $results['order_states'][] = array(
                'id_order_state' => $order_state['id_order_state'],
                'name' => $order_state['name'],
            );
        }

        $this->respondAsJson($results);
    }
}
