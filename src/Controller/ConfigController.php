<?php

namespace Oyst\Controller;

use Configuration;

class ConfigController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('config');
    }

    public function getConfig()
    {
        if (Configuration::hasKey('OYST_CONFIG_CACHE')) {
            $this->respondAsJson(Configuration::get('OYST_CONFIG_CACHE'), true);
        } else {
            $this->respondError(400, "No config stored");
        }
    }

    public function setConfig($params)
    {
        if (Configuration::updateValue('OYST_CONFIG_CACHE', json_encode($params['data']))) {
            $this->respondAsJson('OK');
        } else {
            $this->respondError(400, "Error on save");
        }
    }
}
