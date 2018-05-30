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
    // Get old configuration
    Configuration::updateValue('FC_OYST_WIDTH_BTN_PRODUCT', Configuration::get('FC_OYST_WIDTH_BTN'));
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_PRODUCT', Configuration::get('FC_OYST_HEIGHT_BTN'));
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_PRODUCT', Configuration::get('FC_OYST_MARGIN_TOP_BTN'));
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_PRODUCT', Configuration::get('FC_OYST_MARGIN_LEFT_BTN'));
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_PRODUCT', Configuration::get('FC_OYST_MARGIN_RIGHT_BTN'));
    Configuration::updateValue('FC_OYST_POSITION_BTN_PRODUCT', Configuration::get('FC_OYST_POSITION_BTN'));
    Configuration::updateValue('FC_OYST_ID_BTN_PRODUCT', Configuration::get('FC_OYST_ID_BTN_ADD_TO_CART'));
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_PRODUCT', Configuration::get('FC_OYST_ID_SMART_BTN'));

    // Add conf custom btn cart
    Configuration::updateValue('FC_OYST_POSITION_BTN_CART', 'before');

    if (_PS_VERSION_ >= '1.6.0.0') {
        if (!Configuration::get('FC_OYST_ID_BTN_CART')) {
            Configuration::updateValue('FC_OYST_ID_BTN_CART', '.cart_navigation .button-medium');
        }
        Configuration::updateValue('FC_OYST_ID_SMART_BTN_CART', '.cart_navigation .button-medium');
    } else {
        if (!Configuration::get('FC_OYST_ID_BTN_CART')) {
            Configuration::updateValue('FC_OYST_ID_BTN_CART', '.cart_navigation .exclusive');
        }
        Configuration::updateValue('FC_OYST_ID_SMART_BTN_CART', '.cart_navigation .exclusive');
    }

    // Add conf custom btn layer
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_LAYER', '#layer_cart .button-container');

    // Add conf custom btn payment
    Configuration::updateValue('FC_OYST_BTN_PAYMENT', 0);
    Configuration::updateValue('FC_OYST_WIDTH_BTN_PAYMENT', '');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_PAYMENT', '');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_PAYMENT', '');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_PAYMENT', '');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_PAYMENT', '');
    Configuration::updateValue('FC_OYST_ID_BTN_PAYMENT', '#HOOK_PAYMENT');
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_PAYMENT', '.payment_module');
    Configuration::updateValue('FC_OYST_POSITION_BTN_PAYMENT', 'before');

    // Add conf custom btn form address
    Configuration::updateValue('FC_OYST_BTN_ADDR', 0);
    Configuration::updateValue('FC_OYST_WIDTH_BTN_ADDR', '');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_ADDR', '');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_ADDR', '');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_ADDR', '');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_ADDR', '');
    Configuration::updateValue('FC_OYST_ID_BTN_ADDR', '#submitAddress');
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_ADDR', '#submitAddress');
    Configuration::updateValue('FC_OYST_POSITION_BTN_ADDR', 'before');

    return true;
}
