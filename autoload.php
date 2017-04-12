<?php

$composer = __DIR__.'/vendor/autoload.php';
if (file_exists($composer)) {
    require_once $composer;
}

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

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
 * Include Oyst Product Class
 */
if (!class_exists('OystProductOld', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystProductOld.php';
}

/*
 * Include Oyst Payment Notification Class
 */
if (!class_exists('OystPaymentNotification', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystPaymentNotification.php';
}

define('_PS_OYST_DEBUG_', 0);

// Maybe implements an autoload or use composer if php 5.3 is possible
require_once __DIR__.'/classes/Service/AbstractOystService.php';
require_once __DIR__.'/classes/Repository/AbstractOystRepository.php';
require_once __DIR__.'/classes/Service/ExportProductService.php';
require_once __DIR__.'/classes/Repository/ProductRepository.php';
require_once __DIR__.'/controllers/front/ExportProductController.php';

// Include Oyst library auto load
$autoload = __DIR__.'/external/oyst-library/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

