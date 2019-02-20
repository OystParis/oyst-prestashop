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

use Oyst\Service\Configuration;
use Configuration as PSConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function upgrade_module_1_30_0()
{
    PSConfiguration::deleteByName('OYST_API_ENV_ONECLICK');
    PSConfiguration::updateValue(Configuration::ONE_CLICK_MODE, 'test');

    $oyst = new Oyst();

    $sql = "SELECT `id_hook` 
        FROM `'._DB_PREFIX_.'hook`
        WHERE `name` = 'displayOrderConfirmation'";
    $id_hook = Db::getInstance()->getValue($sql);
    $oyst->unregisterHook($id_hook);
}
