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

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookGetConfigurationProcessor extends FroggyHookProcessor
{
    public $configuration_result = '';
    public $configurations = array(
        'FC_OYST_API_KEY'              => 'string',
        'FC_OYST_API_CHECK_ENDPOINT'   => 'string',
        'FC_OYST_PAYMENT_FEATURE'      => 'int',
        'FC_OYST_API_PAYMENT_ENDPOINT' => 'string',
        'FC_OYST_CATALOG_FEATURE'      => 'int',
        'FC_OYST_API_CATALOG_ENDPOINT' => 'string',
    );

    public function init()
    {
        if (Configuration::get('FC_OYST_HASH_KEY') == '') {
            Configuration::updateValue('FC_OYST_HASH_KEY', md5(rand()._RIJNDAEL_IV_).'-'.date('YmdHis'));
        }
    }

    public function saveModuleConfiguration()
    {
        if (Tools::isSubmit('submitOystConfiguration')) {
            foreach ($this->configurations as $conf => $format) {
                if (is_array($format)) {
                    $value = '';
                    if ($format['type'] == 'multiple') {
                        $values = Tools::getIsset($format['field']) ? Tools::getValue($format['field']) : '';
                        if (is_array($values)) {
                            $values = array_map('intval', $values);
                            $value = implode(',', $values);
                        }
                    }
                } else {
                    $value = Tools::getValue($conf);
                    if ($format == 'int') {
                        $value = (int)$value;
                    } else if ($format == 'float') {
                        $value = (float)$value;
                    }
                }
                Configuration::updateValue($conf, $value);
            }
            $this->configuration_result = 'ok';
        }
    }

    public function displayModuleConfiguration()
    {
        $goToConf = (bool) Tools::getValue('go_to_conf');
        $goToForm = (bool) Tools::getValue('go_to_form');

        if (!$goToForm && !$goToConf) {
            $goToForm = Configuration::get('FC_OYST_API_KEY') == '';
        }

        if ($goToConf) {
            $goToForm = false;
        }

        if (Tools::isSubmit('form_get_apikey_submit')) {
            Configuration::updateValue('FC_OYST_GUEST', true);

            $goToForm = false;

            $name  = Tools::getValue('form_get_apikey_name');
            $phone = Tools::getValue('form_get_apikey_phone');
            $email = Tools::getValue('form_get_apikey_email');

            $response = OystSDK::notifyOnSlack($name, $phone, $email);

            if ($response != 'ok') {
                $goToForm = true;
                // show error!
            } else {
                Configuration::updateValue('FC_OYST_MERCHANT_PHONE', $phone);
            }
        }

        if (Configuration::get('FC_OYST_GUEST')) {
            $dayOfTheWeek    = date('w');
            $currentDateTime = date('Hi');
            $message         = $this->module->l('A FreePay customer advisor shall contact you on');

            if (in_array($dayOfTheWeek, [0, 1, 2, 3, 4]) && $currentDateTime > '2000') {// Dimanche ou Lundi, Mardi, Mercredi, Jeudi après 20h
                $message = $this->module->l('A FreePay customer advisor shall contact you from tomorrow morning 8:30 am on');
            } elseif (in_array($dayOfTheWeek, [1, 2, 3, 4, 5]) && $currentDateTime > '0000' && $currentDateTime < '0830') {// Lundi, Mardi, Mercredi, Jeudi, Vendredi entre 0h01 et 8h30
                $message = $this->module->l('A FreePay customer advisor shall contact you this morning from 8:30 am on');
            } elseif ($dayOfTheWeek == 5 && $currentDateTime > '1800' || $dayOfTheWeek == 6) {// Vendredi après 18h et samedi
                $message = $this->module->l('A FreePay customer advisor shall contact you monday morning from 8:30 am on');
            } elseif (in_array($dayOfTheWeek, [1, 2, 3, 4, 5]) && $currentDateTime > '1200' && $currentDateTime < '1400') {// Lundi, Mardi, Mercredi, Jeudi, Vendredi entre 12h et 14h
                $message = $this->module->l('A FreePay customer advisor shall contact you this afternoon from 14:30 pm on');
            }

            $this->smarty->assign('message', $message);
            $this->smarty->assign('phone', Configuration::get('FC_OYST_MERCHANT_PHONE'));
        }

        $assign = array();
        $assign['module_dir'] = $this->path;
        foreach ($this->configurations as $conf => $format) {
            $assign[$conf] = Configuration::get($conf);
        }

        $assign['result'] = $this->configuration_result;
        $assign['ps_version'] = Tools::substr(_PS_VERSION_, 0, 3);
        $assign['module_version'] = $this->module->version;

        $assign['allow_url_fopen_check'] = ini_get('allow_url_fopen');
        $assign['curl_check'] = function_exists('curl_version');

        $assign['payment_notification_url'] = $this->context->link->getModuleLink('oyst', 'paymentNotification').'?key='.Configuration::get('FC_OYST_HASH_KEY');
        $assign['notification_url'] = $this->context->link->getModuleLink('oyst', 'notification').'?key='.Configuration::get('FC_OYST_HASH_KEY');

        if (Configuration::get('FC_OYST_API_KEY') != '') {
            $oyst_api = new OystSDK();
            $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_CHECK_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $assign['oyst_connection_test'] = $oyst_api->checkApiKey();

            // First time merchant enter a key
            if (Configuration::get('FC_OYST_GUEST')) {
                Configuration::updateValue('FC_OYST_GUEST', false);
            }
        }

        $this->smarty->assign($this->module->name, $assign);
        $this->smarty->assign('configureLink', $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name);


        if ($goToForm) {
            return $this->module->fcdisplay(__FILE__, 'getGuestConfigure.tpl');
        }

        return $this->module->fcdisplay(__FILE__, 'getMerchantConfigure.tpl');
    }

    public function run()
    {
        $this->init();
        $this->saveModuleConfiguration();
        return $this->displayModuleConfiguration();
    }
}
