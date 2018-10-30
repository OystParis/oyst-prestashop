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

    public function getEcommerce()
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

    public function setOyst($params)
    {
        $res = Configuration::updateValue('OYST_MERCHANT_ID', $params['data']['merchant_id']);
        $res &= Configuration::updateValue('OYST_SCRIPT_TAG', base64_encode($params['data']['script_tag'])); //Encode in base64 because prestashop fucked up html tag
        $public_endpoints = [];
        if (!empty($params['data']['public_endpoints'])) {
            $public_endpoints = $params['data']['public_endpoints'];
        }
        $res &= Configuration::updateValue('OYST_PUBLIC_ENDPOINTS', json_encode($public_endpoints));

        if ($res) {
            $this->respondAsJson(array('success' => true));
        } else {
            $this->respondError(400, "Error on update");
        }
    }
}
