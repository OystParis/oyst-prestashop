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
function upgrade_module_1_10_0()
{
    Db::getInstance()->execute(
        "ALTER TABLE "._DB_PREFIX_."oyst_payment_notification ADD `status` varchar(255) DEFAULT NULL"
    );
    Db::getInstance()->execute(
        "ALTER TABLE "._DB_PREFIX_."oyst_payment_notification ADD `date_upd` datetime DEFAULT NULL"
    );
    Configuration::updateValue('FC_OYST_ID_BTN_ADD_TO_CART', '#add_to_cart');

    Configuration::deleteByName('FC_OYST_BORDER_BTN_CART');
    Configuration::deleteByName('FC_OYST_SMART_BTN_CART');
    Configuration::deleteByName('FC_OYST_THEME_BTN_CART');
    Configuration::deleteByName('FC_OYST_COLOR_BTN_CART');
    Configuration::deleteByName('FC_OYST_PREORDER_FEATURE');

    return true;
}
