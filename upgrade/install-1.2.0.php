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
function upgrade_module_1_2_0($module)
{
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
    if (Configuration::get('FC_OYST_API_KEY') != '' && Configuration::get('OYST_API_PROD_KEY_FREEPAY') == '') {
        Configuration::updateValue('OYST_API_PROD_KEY_FREEPAY', Configuration::get('FC_OYST_API_KEY'));
    }

    Configuration::updateValue('OYST_API_ENV', \Oyst\Service\Configuration::API_ENV_PROD);

    // TODO: We should not know about this values.. They belongs to the api client
    Configuration::updateValue('OYST_ONECLICK_URL_PROD', 'https://cdn.oyst.com');
    Configuration::updateValue('OYST_ONECLICK_URL_SANDBOX', 'https://cdn.sandbox.oyst.eu');

    $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), $module);
    $oystDb->createExportTable();

    // As hook are handled dynamically by parent lib, we don't need to move them elsewhere
    $module->registerHook('displayFooterProduct');
    $module->registerHook('displayBackOfficeHeader');

    // All went well!
    return true;
}
