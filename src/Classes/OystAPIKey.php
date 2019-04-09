<?php

namespace Oyst\Classes;

use Configuration as PSConfiguration;
use Tools;

class OystAPIKey
{
	private static $instances = [];
	private $shop_group_id;
	private $shop_id;

	const CONFIG_KEY = 'OYST_API_KEY';

	public static function getShopInstance($shop_group_id, $shop_id)
	{
		$key = $shop_group_id.'-'.$shop_id;
		if (!isset(self::$instances[$key])) {
			self::$instances[$key] = new OystAPIKey($shop_group_id, $shop_id);
		}
		return self::$instances[$key];
	}

	private function __construct($shop_group_id, $shop_id) {
		$this->shop_group_id = $shop_group_id;
		$this->shop_id = $shop_id;
	}

	private function __clone() {}

    public function generateAPIKey($force = false)
    {
		if (!PSConfiguration::hasKey(self::CONFIG_KEY, false, $this->shop_group_id, $this->shop_id) || $force) {
        	return $this->setAPIKey(Tools::passwdGen(32));
		}
		return true;
    }

    public function setAPIKey($key)
    {
        return PSConfiguration::updateValue(self::CONFIG_KEY, $key, false, $this->shop_group_id, $this->shop_id);
    }

    public function getAPIKey()
    {
        if (PSConfiguration::hasKey(self::CONFIG_KEY, false, $this->shop_group_id, $this->shop_id)) {
            return PSConfiguration::get(self::CONFIG_KEY, false, $this->shop_group_id, $this->shop_id);
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
