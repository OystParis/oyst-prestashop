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
function upgrade_module_1_7_1()
{
    Configuration::updateValue('OYST_ONECLICK_URL_SANDBOX', 'https://cdn.sandbox.oyst.eu');
    Configuration::updateValue('FC_OYST_MANAGE_QUANTITY', 1);
    Configuration::updateValue('FC_OYST_SHOULD_AS_STOCK', 1);
    Configuration::updateValue('FC_OYST_BORDER_BTN', 1);
    Configuration::updateValue('FC_OYST_SMART_BTN', 1);
}
