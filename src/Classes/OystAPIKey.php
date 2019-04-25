<?php

namespace Oyst\Classes;

use Configuration;
use Tools;

class OystAPIKey
{
	private static $instance;
	const CONFIG_KEY = 'OYST_API_KEY';

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new OystAPIKey();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function __clone() {}

    public function generateAPIKey($force = false)
    {
		if (!Configuration::hasKey(self::CONFIG_KEY) || $force) {
        	return $this->setAPIKey(Tools::passwdGen(32));
		}
		return true;
    }

    public function setAPIKey($key)
    {
        return Configuration::updateGlobalValue(self::CONFIG_KEY, $key);
    }

    public function getAPIKey()
    {
        if (Configuration::hasKey(self::CONFIG_KEY)) {
            return Configuration::getGlobalValue(self::CONFIG_KEY);
        } else {
            return '';
        }
    }

    public function isKeyActive($key)
    {
        $api_key = $this->getAPIKey();
        return (!empty($api_key) && $api_key == $key);
    }
}
