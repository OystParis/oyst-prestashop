<?php

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

// When sending the module to PrestaShop Validator, all the require files will be available
if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    throw new Exception('Please install composer inside the oyst module');
}

require_once __DIR__.'/vendor/autoload.php';

// When sending the module to PrestaShop Validator, all the require files will be available
if (!file_exists(__DIR__.'/external/oyst-library/autoload.php')) {
    throw new Exception('Please install composer inside the external/oyst-library folder');
}

require_once __DIR__.'/external/oyst-library/autoload.php';

/*
 * Include Froggy Library
 */
if (!class_exists('FroggyModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyModule.php';
}
if (!class_exists('FroggyPaymentModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyPaymentModule.php';
}

/*
 * Include Oyst SDK
 */
if (!class_exists('OystSDK', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystSDK.php';
}

/*
 * Include Oyst Payment Notification Class
 */
if (!class_exists('OystPaymentNotification', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystPaymentNotification.php';
}

define('_PS_OYST_DEBUG_', 0);
