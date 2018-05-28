<?php

namespace Oyst\Controller;

use Carrier;
use Country;
use Language;

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
        $carriers = Carrier::getCarriers($id_lang, false, false, false, null, Carrier::ALL_CARRIERS);
        foreach ($carriers as $carrier) {
            $results['carriers'][] = array(
                'id_carrier' => $carrier['id_carrier'],
                'name' => $carrier['name'],
            );
        }

        //Get countries
        $countries = Country::getCountries($id_lang);
        foreach ($countries as $country) {
            $results['countries'][] = array(
                'id_country' => $country['id_country'],
                'name' => $country['name'],
                'iso_code' => $country['iso_code'],
            );
        }
        $this->respondAsJson($results);
    }
}
