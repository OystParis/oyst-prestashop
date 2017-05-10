<?php

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
 * Include Oyst Payment Notification Class
 */
if (!class_exists('OystPaymentNotification', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystPaymentNotification.php';
}

/*
 * Include Oyst Repository
 */
if (!class_exists('AbstractOystRepository', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/src/Repository/AbstractOystRepository.php';
}
if (!class_exists('OrderRepository', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/src/Repository/OrderRepository.php';
}

define('_PS_OYST_DEBUG_', 0);
