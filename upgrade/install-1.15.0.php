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

use Oyst\Service\Configuration as OConfiguration;

/**
 * @param Oyst $module
 * @return bool
 */
function upgrade_module_1_15_0($module)
{
    $module->registerHook('actionValidateOrder');

    $module->unregisterHook('displayFooterProduct');
    $module->unregisterHook('displayShoppingCart');
    $module->registerHook('displayFooter');

    // Remove old configuration button product
    Configuration::deleteByName('FC_OYST_BTN_PRODUCT');
    Configuration::deleteByName('FC_OYST_WIDTH_BTN');
    Configuration::deleteByName('FC_OYST_HEIGHT_BTN');
    Configuration::deleteByName('FC_OYST_MARGIN_TOP_BTN');
    Configuration::deleteByName('FC_OYST_MARGIN_LEFT_BTN');
    Configuration::deleteByName('FC_OYST_MARGIN_RIGHT_BTN');
    Configuration::deleteByName('FC_OYST_POSITION_BTN');
    Configuration::deleteByName('FC_OYST_ID_BTN_ADD_TO_CART');
    Configuration::deleteByName('FC_OYST_ID_SMART_BTN');

    // Rename configuration button product
    Configuration::updateValue('FC_OYST_BTN_PRODUCT', 1);
    Configuration::updateValue('FC_OYST_WIDTH_BTN_PRODUCT');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_PRODUCT');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_PRODUCT');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_PRODUCT');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_PRODUCT');
    Configuration::updateValue('FC_OYST_POSITION_BTN_PRODUCT', 'before');
    Configuration::updateValue('FC_OYST_ID_BTN__PRODUCT', '#add_to_cart');
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_PRODUCT', '#add_to_cart button');

    // Add conf custom btn layer
    Configuration::updateValue('FC_OYST_BTN_LAYER', 0);
    Configuration::updateValue('FC_OYST_WIDTH_BTN_LAYER', '214');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_LAYER', '43');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_LAYER', '');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_LAYER', '');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_LAYER', '');
    Configuration::updateValue('FC_OYST_ID_BTN_LAYER', '#layer_cart .button-container');
    Configuration::updateValue('FC_OYST_POSITION_BTN_LAYER', 'before');

    // Add conf custom btn login
    Configuration::updateValue('FC_OYST_BTN_LOGIN', 0);
    Configuration::updateValue('FC_OYST_WIDTH_BTN_LOGIN', '');
    Configuration::updateValue('FC_OYST_HEIGHT_BTN_LOGIN', '');
    Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_LOGIN', '');
    Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_LOGIN', '');
    Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_LOGIN', '');
    Configuration::updateValue('FC_OYST_ID_BTN_LOGIN', '#center_column');
    Configuration::updateValue('FC_OYST_ID_SMART_BTN_LOGIN', '#SubmitCreate');
    Configuration::updateValue('FC_OYST_POSITION_BTN_LOGIN', 'before');

    return true;
}
