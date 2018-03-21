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
function upgrade_module_1_9_1()
{
    Configuration::updateValue('FC_OYST_MANAGE_QUANTITY_CART', 0);
    Configuration::updateValue('FC_OYST_BTN_PRODUCT', 1);
    Configuration::updateValue('FC_OYST_OC_REDIRECT_CONF', '');
    Configuration::updateValue('FC_OYST_OC_REDIRECT_CONF_CUSTOM', '');

    // Add conf custom btn cart
    Configuration::updateValue('FC_OYST_THEME_BTN_CART', '');
    Configuration::updateValue('FC_OYST_COLOR_BTN_CART', '#E91E63');
    Configuration::updateValue('FC_OYST_WIDTH_BTN_CART', '');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_CART', '');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_CART', '');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_CART', '');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_CART', '');
    Configuration::updateValue('FC_OYST_ID_BTN_CART', '.standard-checkout');
    Configuration::updateValue('FC_OYST_BORDER_BTN_CART', '');
    Configuration::updateValue('FC_OYST_SMART_BTN_CART', '');
    Configuration::updateValue('FC_OYST_CUSTOM_CSS', '');

    return true;
}
