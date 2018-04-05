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
function upgrade_module_1_3_0($module)
{
    // Migrate constants to new ones
    $currentEnv = Configuration::get('OYST_API_ENV');
    if (!$currentEnv || empty($currentEnv) ||
        !in_array($currentEnv, array(
            OConfiguration::API_ENV_PROD,
            OConfiguration::API_ENV_SANDBOX,
            OConfiguration::API_ENV_CUSTOM
        ))
    ) {
        $currentEnv = OConfiguration::API_ENV_PROD;
    }
    Configuration::updateValue(OConfiguration::API_ENV_FREEPAY, $currentEnv);
    Configuration::updateValue(OConfiguration::API_ENV_ONECLICK, $currentEnv);

    $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), $module);
    $oystDb->createOrderTable();

    $module->registerHook('actionOrderStatusPostUpdate');
    $module->registerHook('actionProductAdd');
    $module->registerHook('actionProductUpdate');
    $module->registerHook('actionObjectProductDeleteAfter');

    Configuration::updateValue('OYST_API_ENV_FREEPAY', Configuration::get('OYST_API_ENV'));
    Configuration::updateValue('OYST_API_ENV_ONECLICK', Configuration::get('OYST_API_ENV'));
    Configuration::deleteByName('OYST_API_ENV');

    Configuration::updateValue('OYST_API_CUSTOM_ENDPOINT_FREEPAY', Configuration::get('OYST_API_CUSTOM_ENDPOINT'));
    Configuration::updateValue('OYST_API_CUSTOM_ENDPOINT_ONECLCK', Configuration::get('OYST_API_CUSTOM_ENDPOINT'));
    Configuration::deleteByName('OYST_API_CUSTOM_ENDPOINT');

    return true;
}
