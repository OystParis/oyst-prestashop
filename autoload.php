<?php

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

// When sending the module to PrestaShop Validator, all the require files will be available
if (!file_exists(dirname(__FILE__).'/vendor/autoload.php')) {
    throw new Exception('Please install composer inside the oyst module');
}

require_once dirname(__FILE__).'/vendor/autoload.php';

define('_PS_OYST_DEBUG_', 0);
