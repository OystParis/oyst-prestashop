<?php

namespace Oyst\Controller;

use Carrier;
use Configuration;
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
                'label' => $carrier['name'],
                'reference' => $carrier['id_reference'],
                'delivery_delay' => $carrier['delay'],
            );
        }

        //Get countries
        $countries = Country::getCountries($id_lang, true);
        foreach ($countries as $country) {
            $results['countries'][] = array(
                'name' => $country['name'],
                'code' => $country['iso_code'],
            );
        }

        //Get status
        $order_states = OrderState::getOrderStates($id_lang);
        foreach ($order_states as $order_state) {
            $results['order_statuses'][] = array(
                'label' => $order_state['name'],
                'code' => $order_state['id_order_state'],
            );
        }

        $this->respondAsJson($results);
    }

    public function setScriptTagUrl($params)
    {
        if (Configuration::updateValue('OYST_SCRIPT_TAG_URL', $params['data']['url'])) {
            $this->respondAsJson(array('success' => true));
        } else {
            $this->respondError(400, "Error on update");
        }
    }
}
