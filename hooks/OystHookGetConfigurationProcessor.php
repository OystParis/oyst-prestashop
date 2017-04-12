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
use Oyst\Repository\ProductRepository;
use Oyst\Service\ExportProductService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookGetConfigurationProcessor extends FroggyHookProcessor
{
    public $configuration_result = '';
    public $configurations = array(
        'FC_OYST_GUEST'                   => 'int',
        'FC_OYST_REDIRECT_SUCCESS'        => 'string',
        'FC_OYST_REDIRECT_ERROR'          => 'string',
        'FC_OYST_REDIRECT_SUCCESS_CUSTOM' => 'string',
        'FC_OYST_REDIRECT_ERROR_CUSTOM'   => 'string',
        'FC_OYST_API_PROD_KEY' => 'string',
        'FC_OYST_API_PREPROD_KEY' => 'string',
        'FC_OYST_API_INTEGRATION_KEY' => 'string',
        'FC_OYST_API_ENV' => 'string',
        'FC_OYST_PAYMENT_FEATURE'         => 'int',
        'FC_OYST_API_PAYMENT_ENDPOINT'    => 'string',
        'FC_OYST_CATALOG_FEATURE'         => 'int',
        'FC_OYST_API_CATALOG_ENDPOINT'    => 'string',
    );
    public $redirect_success_urls = array();
    public $redirect_error_urls = array();

    public function init()
    {
        if (Configuration::get('FC_OYST_HASH_KEY') == '') {
            Configuration::updateValue('FC_OYST_HASH_KEY', md5(rand()._RIJNDAEL_IV_).'-'.date('YmdHis'));
        }

        $this->redirect_success_urls = array(
            'ORDER_HISTORY'      => $this->module->l('Order history', 'oysthookgetconfigurationprocessor'),
            'ORDER_CONFIRMATION' => $this->module->l('Order confirmation', 'oysthookgetconfigurationprocessor'),
            'CUSTOM'             => $this->module->l('Custom', 'oysthookgetconfigurationprocessor')
        );
        $this->redirect_error_urls = array(
            'ORDER_HISTORY' => $this->module->l('Order history', 'oysthookgetconfigurationprocessor'),
            'PAYMENT_ERROR' => $this->module->l('Payment error', 'oysthookgetconfigurationprocessor'),
            'CART'          => $this->module->l('Cart', 'oysthookgetconfigurationprocessor'),
            'CUSTOM'        => $this->module->l('Custom', 'oysthookgetconfigurationprocessor')
        );
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
        $assign      = array();
        $apiKey      = Configuration::get('FC_OYST_API_KEY');
        $clientPhone = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $goToConf    = (bool) Tools::getValue('go_to_conf');
        $goToForm    = (bool) Tools::getValue('go_to_form');
        $hasError    = false;

        // Merchant comes from the plugin list
        if (!$goToForm && !$goToConf) {
            $goToForm = $clientPhone == '' && $apiKey == '';
        }

        if ($goToConf) {
            Configuration::updateValue('FC_OYST_GUEST', false);
            $goToForm = false;
        }

        $isOystDeveloper = filter_var(getenv('OYST_DEVELOPER'), FILTER_VALIDATE_BOOLEAN);

        $this->handleContactForm($assign, $hasError, $goToForm);

        $assign['isOystDeveloper'] = $isOystDeveloper;
        $assign['exportRunning']            = $this->module->isCatalogExportStillRunning();
        $assign['module_dir']               = $this->path;
        $assign['message']                  = '';
        $assign['phone']                    = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $assign['apikey_test_error']        = '';
        $assign['result']                   = $this->configuration_result;
        $assign['ps_version']               = Tools::substr(_PS_VERSION_, 0, 3);
        $assign['module_version']           = $this->module->version;
        $assign['allow_url_fopen_check']    = ini_get('allow_url_fopen');
        $assign['curl_check']               = function_exists('curl_version');
        $assign['payment_notification_url'] = $this->context->link->getModuleLink('oyst', 'paymentNotification').'?key='.Configuration::get('FC_OYST_HASH_KEY');
        $assign['notification_url']         = $this->context->link->getModuleLink('oyst', 'notification').'?key='.Configuration::get('FC_OYST_HASH_KEY');
        $assign['configureLink']            = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;
        $assign['redirect_success_urls']    = $this->redirect_success_urls;
        $assign['redirect_error_urls']      = $this->redirect_error_urls;
        $assign['custom_success_error']     = !Validate::isAbsoluteUrl(Configuration::get('FC_OYST_REDIRECT_SUCCESS_CUSTOM'));
        $assign['custom_error_error']       = !Validate::isAbsoluteUrl(Configuration::get('FC_OYST_REDIRECT_ERROR_CUSTOM'));

        $clientPhone = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $isGuest     = Configuration::get('FC_OYST_GUEST');

        if ($isGuest && $clientPhone) {
            $this->showMessageToMerchant($assign);
        }

        if ($apiKey != '') {
            $assign['apikey_test_error'] = Tools::strlen($apiKey) != 64;

            // First time merchant enter a key after submitting the contact form
            if ($isGuest) {
                Configuration::updateValue('FC_OYST_GUEST', false);
            }
        }

        foreach ($this->configurations as $conf => $format) {
            $assign[$conf] = Configuration::get($conf);
        }

        $this->smarty->assign($this->module->name, $assign);

        if ($goToForm || $hasError) {
            return $this->module->fcdisplay(__FILE__, 'getGuestConfigure.tpl');
        }

        return $this->module->fcdisplay(__FILE__, 'getMerchantConfigure.tpl');
    }

    private function postRequest()
    {
        if (Tools::isSubmit('synchronizeProducts')) {

            $productRepository = new ProductRepository(Db::getInstance());

            /** @var OystCatalogAPI $oystCatalogAPI */
            $oystCatalogAPI = OystApiClientFactory::getClient(
                OystApiClientFactory::ENTITY_CATALOG,
                $this->module->getApiKey(),
                'PrestaShop-'.$this->module->version,
                $this->module->getEnvironment()
            );

            (new ExportProductService(Context::getContext(), $this->module))
                ->setRepository($productRepository)
                ->setCatalogApi($oystCatalogAPI)
                ->requestNewExport()
            ;
        }
    }

    public function run()
    {
        $this->init();
        $this->postRequest();
        $this->saveModuleConfiguration();
        return $this->displayModuleConfiguration();
    }

    /**
     * @param $assign
     * @param $hasError
     * @param $goToForm
     */
    private function handleContactForm(&$assign, &$hasError, &$goToForm)
    {
        $assign['form_get_apikey_name']  = $this->context->employee->lastname.' '.$this->context->employee->firstname;
        $assign['form_get_apikey_phone'] = '';
        $assign['form_get_apikey_email'] = $this->context->employee->email;
        $assign['form_get_apikey_name_error']  = '';
        $assign['form_get_apikey_phone_error'] = '';
        $assign['form_get_apikey_email_error'] = '';

        // Merchant filled the contact form
        if (!Tools::isSubmit('form_get_apikey_submit')) {
            return;
        }

        $goToForm = false;

        $name  = Tools::getValue('form_get_apikey_name');
        $phone = Tools::getValue('form_get_apikey_phone');
        $email = Tools::getValue('form_get_apikey_email');

        $assign['form_get_apikey_name']  = $name;
        $assign['form_get_apikey_phone'] = $phone;
        $assign['form_get_apikey_email'] = $email;

        $nameError  = '';
        $phoneError = '';
        $emailError = '';

        if (!$name || !Validate::isName($name)) {
            $hasError  = true;
            $nameError = $this->module->l('Please enter a valid name', 'oysthookgetconfigurationprocessor');
        }

        if (!$phone || !Validate::isPhoneNumber($phone)) {
            $hasError   = true;
            $phoneError = $this->module->l('Please enter your phone number in the format 06 00 00 00 00', 'oysthookgetconfigurationprocessor');
        }

        if (!$email || !Validate::isEmail($email)) {
            $hasError   = true;
            $emailError = $this->module->l('Please enter a valid email', 'oysthookgetconfigurationprocessor');
        }

        $assign['form_get_apikey_name_error']   = $nameError;
        $assign['form_get_apikey_phone_error']  = $phoneError;
        $assign['form_get_apikey_email_error']  = $emailError;
        $assign['form_get_apikey_notify_error'] = false;

        if (!$hasError) {
            $isSent = OystSDK::notify($name, $phone, $email);

            if (!$isSent) {
                $goToForm = true;
                $assign['form_get_apikey_notify_error'] = true;
            } else {
                Configuration::updateValue('FC_OYST_GUEST', true);
                Configuration::updateValue('FC_OYST_MERCHANT_PHONE', $phone);
            }
        }
    }

    /**
     * Show a different message to the merchant according to the day of the week and the time
     */
    private function showMessageToMerchant(&$assign)
    {
        $dayOfTheWeek    = date('w');
        $currentDateTime = date('Hi');
        $message         = $this->module->l('A FreePay customer advisor shall contact you on', 'oysthookgetconfigurationprocessor');

        if (in_array($dayOfTheWeek, array(0, 1, 2, 3, 4)) && $currentDateTime > '2000') {// Sunday or Monday, Tuesday, Wednesday, Thursday after 8pm
            $message = $this->module->l('A FreePay customer advisor shall contact you from tomorrow morning 8:30 am on', 'oysthookgetconfigurationprocessor');
        } elseif (in_array($dayOfTheWeek, array(1, 2, 3, 4, 5)) && $currentDateTime > '0000' && $currentDateTime < '0830') {// Monday, Tuesday, Wednesday, Thursday, Friday between 12:01 am and 8:30 am
            $message = $this->module->l('A FreePay customer advisor shall contact you this morning from 8:30 am on', 'oysthookgetconfigurationprocessor');
        } elseif ($dayOfTheWeek == 5 && $currentDateTime > '1800' || $dayOfTheWeek == 6) {// Friday after 6 pm and Saturday
            $message = $this->module->l('A FreePay customer advisor shall contact you monday morning from 8:30 am on', 'oysthookgetconfigurationprocessor');
        } elseif (in_array($dayOfTheWeek, array(1, 2, 3, 4, 5)) && $currentDateTime > '1200' && $currentDateTime < '1400') {// Monday, Tuesday, Wednesday, Thursday, Friday entre 12h et 14h
            $message = $this->module->l('A FreePay customer advisor shall contact you this afternoon from 14:30 pm on', 'oysthookgetconfigurationprocessor');
        }

        $assign['message'] = $message;
    }
}
