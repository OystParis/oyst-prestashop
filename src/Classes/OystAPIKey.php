<?php

namespace Oyst\Classes;

use Configuration as PSConfiguration;
use Tools;

class OystAPIKey
{
    const CONFIG_KEY = 'OYST_API_KEY';

    public static function generateAPIKey()
    {
        self::setAPIKey(Tools::passwdGen(32));
    }

    public static function setAPIKey($key)
    {
        PSConfiguration::updateValue(self::CONFIG_KEY, $key);
    }

    public static function getAPIKey()
    {
        if (PSConfiguration::hasKey(self::CONFIG_KEY)) {
            return PSConfiguration::get(self::CONFIG_KEY);
        } else {
            return '';
        }
    }

    public static function isKeyActive($key)
    {
        return (!empty(self::getAPIKey()) && self::getAPIKey() == $key);
    }
}
