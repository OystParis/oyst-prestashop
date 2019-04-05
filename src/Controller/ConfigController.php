<?php

namespace Oyst\Controller;

use Carrier;
use Configuration;
use Country;
use Language;
use OrderState;
use Oyst\Services\ConfigService;
use Shop;

class ConfigController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('config');
    }

    public function getEcommerce()
    {
        $results = [];

        $config_service = ConfigService::getInstance();

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

        $results['carriers'] = $config_service->getCarriers();
        $results['countries'] = $config_service->getCountries();
        $results['order_statuses'] = $config_service->getOrderStatuses();

        //Get shops
        $shops = Shop::getShops(false);
        foreach ($shops as $shop) {
            $shop_obj = new Shop($shop['id_shop']);
            $results['shops'][] = array(
                'url' => $shop_obj->getBaseURL(true),
                'code' => $shop_obj->id,
                'label' => $shop_obj->name,
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
