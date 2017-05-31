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

/**
 * @param Oyst $module
 * @return bool
 */
function upgrade_module_1_2_0($module)
{
    $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), $module,_DB_PREFIX_);
    $oystDb->createExportTable();
    $oystDb->createCarrier();

    // As hook are handled dynamically by parent lib, we don't need to move them elsewhere
    $module->registerHook('displayFooterProduct');
    $module->registerHook('displayBackOfficeHeader');

    // If old configuration variable exists, we migrate and delete it
    if (Configuration::get('FC_OYST_API_PAYMENT_KEY') != '' && Configuration::get('FC_OYST_API_KEY') == '') {
        Configuration::updateValue('FC_OYST_API_KEY', Configuration::get('FC_OYST_API_PAYMENT_KEY'));
    }

    Configuration::deleteByName('FC_OYST_API_PAYMENT_KEY');

    // If old configuration variable exists, we migrate and delete it
    if (Configuration::get('FC_OYST_API_CATALOG_KEY') != '' && Configuration::get('FC_OYST_API_KEY') == '') {
        Configuration::updateValue('FC_OYST_API_KEY', Configuration::get('FC_OYST_API_CATALOG_KEY'));
    }

    Configuration::deleteByName('FC_OYST_API_CATALOG_KEY');

    // If old configuration variable exists, we migrate and delete it
    if (Configuration::get('FC_OYST_API_KEY') != '' && Configuration::get('OYST_API_PROD_FREEPAY_KEY') == '') {
        Configuration::updateValue('OYST_API_PROD_FREEPAY_KEY', Configuration::get('FC_OYST_API_KEY'));
    }

    Configuration::deleteByName('FC_OYST_API_KEY');

    // All went well!
    return true;
}
