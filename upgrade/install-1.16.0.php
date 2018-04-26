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

require_once dirname(__FILE__).'/../vendor/autoload.php';

/**
 * @param Oyst $module
 * @return bool
 */
function upgrade_module_1_16_0($module)
{
    // Add conf custom btn cart
    Configuration::updateValue('FC_OYST_POSITION_BTN_CART', 'before');

    if (_PS_VERSION_ >= '1.6.0.0') {
        Configuration::updateValue('FC_OYST_ID_BTN_CART', '.cart_navigation .button-medium');
        Configuration::updateValue('FC_OYST_ID_SMART_BTN_CART', '.cart_navigation .button-medium');
    } else {
        Configuration::updateValue('FC_OYST_ID_BTN_CART', '.cart_navigation .exclusive');
        Configuration::updateValue('FC_OYST_ID_SMART_BTN_CART', '.cart_navigation .exclusive');
    }

    // Add conf custom btn layer
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_LAYER', '#layer_cart .button-container');

    return true;
}
