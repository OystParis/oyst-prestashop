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

if (!defined('_PS_VERSION_'))
    exit;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function upgrade_module_1_5_2($module) {
    $result = true;
    $langId = Configuration::get('PS_LANG_DEFAULT');
    $oyst = new Oyst();

    $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD_CHECK'));

    if (!Validate::isLoadedObject($orderState)) {
        
        $orderState->name = array(
            $langId => 'En attente de check fraud',
        );
        $orderState->color = '#FF8C00';
        $orderState->unremovable = true;
        $orderState->deleted = false;
        $orderState->delivery = false;
        $orderState->invoice = false;
        $orderState->logable = false;
        $orderState->module_name = $oyst->name;
        $orderState->paid = false;
        $orderState->hidden = false;
        $orderState->shipped = false;
        $orderState->send_email = false;
        
        $result &= $orderState->add();

        Configuration::updateValue('OYST_STATUS_FRAUD_CHECK', $orderState->id);
    }

    return $result;
}
