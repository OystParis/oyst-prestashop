<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

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
