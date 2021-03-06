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

use Oyst\Api\OystApiClientFactory;
use Oyst\Factory\AbstractExportProductServiceFactory;
use Oyst\Service\Configuration as OystConfiguration;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Service\Logger\LoggerManager;

class OystHookGetConfigurationProcessor extends FroggyHookProcessor
{
    /** @var string  */
    public $configuration_result = '';

    /** @var array  */
    public $configurations = array(
        'FC_OYST_GUEST'                   => 'int',
        'FC_OYST_REDIRECT_SUCCESS'        => 'string',
        'FC_OYST_REDIRECT_ERROR'          => 'string',
        'FC_OYST_REDIRECT_SUCCESS_CUSTOM' => 'string',
        'FC_OYST_REDIRECT_ERROR_CUSTOM'   => 'string',
        'FC_OYST_PAYMENT_FEATURE'         => 'int',
        'FC_OYST_CATALOG_FEATURE'         => 'int',
        'FC_OYST_SHIPMENT_DEFAULT'        => 'int',
        'FC_OYST_BORDER_BTN'              => 'int',
        'FC_OYST_SMART_BTN'               => 'int',
        'FC_OYST_THEME_BTN'               => 'string',
        'FC_OYST_COLOR_BTN'               => 'string',
        'FC_OYST_WIDTH_BTN'               => 'string',
        'FC_OYST_HEIGHT_BTN'              => 'string',
        'FC_OYST_MARGIN_TOP_BTN'          => 'string',
        'FC_OYST_MARGIN_LEFT_BTN'         => 'string',
        'FC_OYST_MARGIN_RIGHT_BTN'        => 'string',
        'FC_OYST_POSITION_BTN'            => 'string',
        'FC_OYST_ID_BTN_ADD_TO_CART'      => 'string',
        'FC_OYST_ID_SMART_BTN'            => 'string',
        'FC_OYST_WIDTH_BTN_CART'          => 'string',
        'FC_OYST_HEIGHT_BTN_CART'         => 'string',
        'FC_OYST_MARGIN_TOP_BTN_CART'     => 'string',
        'FC_OYST_MARGIN_LEFT_BTN_CART'    => 'string',
        'FC_OYST_MARGIN_RIGHT_BTN_CART'   => 'string',
        'FC_OYST_POSITION_BTN_CART'       => 'string',
        'FC_OYST_ID_BTN_CART'             => 'string',
        'FC_OYST_DELAY'                   => 'int',
        'FC_OYST_STATE_PAYMENT_FREEPAY'   => 'string',
        'FC_OYST_STATE_PAYMENT_ONECLICK'  => 'string',
        'FC_OYST_ACTIVE_FRAUD'            => 'int',
        'FC_OYST_LANG'                    => array('type' => 'multiple', 'field' => 'oyst_lang'),
        'FC_OYST_SHOULD_AS_STOCK'         => 'int',
        'FC_OYST_MANAGE_QUANTITY'         => 'int',
        'FC_OYST_BTN_CART'                => 'int',
        'FC_OYST_MANAGE_QUANTITY_CART'    => 'int',
        'FC_OYST_ONLY_FOR_IP'             => 'string',
        'FC_OYST_BTN_PRODUCT'             => 'int',
        'FC_OYST_OC_REDIRECT_CONF'        => 'string',
        'FC_OYST_OC_REDIRECT_CONF_CUSTOM' => 'string',
        'FC_OYST_CUSTOM_CSS'              => 'string',
        OystConfiguration::API_KEY_PROD_FREEPAY => 'string',
        OystConfiguration::API_KEY_SANDBOX_FREEPAY => 'string',
        OystConfiguration::API_KEY_CUSTOM_FREEPAY => 'string',
        OystConfiguration::API_KEY_PROD_ONECLICK => 'string',
        OystConfiguration::API_KEY_SANDBOX_ONECLICK => 'string',
        OystConfiguration::API_KEY_CUSTOM_ONECLICK => 'string',
        OystConfiguration::API_ENDPOINT_CUSTOM_FREEPAY => 'string',
        OystConfiguration::API_ENDPOINT_CUSTOM_ONECLICK => 'string',
        OystConfiguration::API_ENV_FREEPAY => 'string',
        OystConfiguration::API_ENV_ONECLICK => 'string',
        OystConfiguration::ONE_CLICK_FEATURE_STATE => 'int',
        OystConfiguration::ONE_CLICK_URL_CUSTOM => 'string',
    );

    /** @var array  */
    public $redirect_success_urls = array();

    /** @var array  */
    public $redirect_error_urls = array();

    /** @var array  */
    public $redirect_cancel_urls = array();

    /** @var array  */
    public $redirect_oc_conf_urls = array();

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
        $this->redirect_oc_conf_urls = array(
            // 'ORDER_HISTORY'      => $this->module->l('Order history', 'oysthookgetconfigurationprocessor'),
            'ORDER_CONFIRMATION' => $this->module->l('Order confirmation', 'oysthookgetconfigurationprocessor'),
            'CUSTOM'             => $this->module->l('Custom', 'oysthookgetconfigurationprocessor')
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
                    } elseif ($format == 'float') {
                        $value = (float)$value;
                    }
                }

                Configuration::updateValue($conf, $value);
            }

            $carriers = $this->getCarrierList();

            foreach ($carriers as $carrier) {
                // Update type for each carrier
                $field = 'FC_OYST_SHIPMENT_'.$carrier['id_reference'];
                $type = Tools::getValue($field);
                Configuration::updateValue($field, $type);
                // Update delay for each carrier
                $field_delay = 'FC_OYST_SHIPMENT_DELAY_'.$carrier['id_reference'];
                $delay = Tools::getValue($field_delay);
                Configuration::updateValue($field_delay, $delay);
            }

            $this->configuration_result = true;
        }

        if (Tools::isSubmit('submitOystResetCustomGlobal')) {
            Configuration::updateValue('FC_OYST_BORDER_BTN', 1);
            Configuration::updateValue('FC_OYST_SMART_BTN', 1);
            Configuration::updateValue('FC_OYST_THEME_BTN', 'normal');
            Configuration::updateValue('FC_OYST_COLOR_BTN', '#E91E63');
            Configuration::updateValue('FC_OYST_CUSTOM_CSS', '');
        }

        if (Tools::isSubmit('submitOystResetCustomProduct')) {
            Configuration::updateValue('FC_OYST_BTN_PRODUCT', 1);
            Configuration::updateValue('FC_OYST_WIDTH_BTN', '');
            Configuration::updateValue('FC_OYST_HEIGHT_BTN', '');
            Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN', '');
            Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN', '');
            Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN', '');
            Configuration::updateValue('FC_OYST_POSITION_BTN', 'before');
            Configuration::updateValue('FC_OYST_ID_BTN_ADD_TO_CART', '#add_to_cart');
            Configuration::updateValue('FC_OYST_ID_SMART_BTN', '#add_to_cart button');
        }

        if (Tools::isSubmit('submitOystResetCustomCart')) {
            Configuration::updateValue('FC_OYST_BTN_CART', 0);
            Configuration::updateValue('FC_OYST_WIDTH_BTN_CART', '');
            Configuration::updateValue('FC_OYST_HEIGHT_BTN_CART', '');
            Configuration::updateValue('FC_OYST_MARGIN_TOP_BTN_CART', '');
            Configuration::updateValue('FC_OYST_MARGIN_LEFT_BTN_CART', '');
            Configuration::updateValue('FC_OYST_MARGIN_RIGHT_BTN_CART', '');
            Configuration::updateValue('FC_OYST_ID_BTN_CART', '.standard-checkout');
        }

        if (Tools::isSubmit('submitOystConfigurationReset')) {
            $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), new Oyst());
            $this->configuration_result = $oystDb->truncateProductTable();
        }

        if (Tools::isSubmit('submitOystConfigurationDisable')) {
            $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), new Oyst());
            $oystDb->truncateProductTable();
            $this->configuration_result = $oystDb->disableProductTable();
        }
    }

    /**
     * @return mixed
     */
    public function displayModuleConfiguration()
    {
        $assign = array();
        $currentFreePayApiKey = $this->module->getFreePayApiKey();
        $currentOneClickApiKey = $this->module->getOneClickApiKey();
        $hasApiKey = !empty($currentFreePayApiKey) || !empty($currentOneClickApiKey);

        // Keep it simple for now
        $isCurrentFreePayApiKeyValid = Tools::strlen($currentFreePayApiKey) == 64;
        $isCurrentOneClickApiKeyValid = Tools::strlen($currentOneClickApiKey) == 64;

        $clientPhone = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $goToConf    = (bool) Tools::getValue('go_to_conf');
        $goToForm    = (bool) Tools::getValue('go_to_form');
        $hasError    = false;
        $id_lang = $this->context->language->id;

        // Merchant comes from the plugin list
        if (!$hasApiKey && !$goToConf) {
            $goToForm = empty($clientPhone) && empty($hasApiKey);
        }

        if ($goToConf) {
            Configuration::updateValue('FC_OYST_GUEST', false);
            $goToForm = false;
        }

        $this->handleContactForm($assign, $hasError, $goToForm);

        $catalogApi = OystApiClientFactory::getClient(
            OystApiClientFactory::ENTITY_CATALOG,
            $this->module->getOneClickApiKey(),
            $this->module->getUserAgent(),
            $this->module->getOneClickEnvironment(),
            $this->module->getCustomOneClickApiUrl()
        );
        $result = $catalogApi->getShipmentTypes();
        $shipmentTypes = array();

        if (isset($result['types'])) {
            foreach ($result['types'] as $value => $label) {
                $shipmentTypes[$value] = $this->module->l($label, 'oysthookgetconfigurationprocessor');
            }
        }

        $loggerManager = new LoggerManager();
        $logsFile = $loggerManager->getFiles();
        $filesName = array();
        foreach ($logsFile as $logFile) {
            $filesName[] = basename($logFile);
        }

        // Get restrictions lang
        $languages = Configuration::get('FC_OYST_LANG');
        if ($languages != null || $languages != '') {
            if (false  !== strpos($languages, ',')) {
                $restriction_languages = explode(',', $languages);
            } else {
                $restriction_languages = array($languages);
            }
        } else {
            $restriction_languages = array();
        }

        // Table list for notification datatable
        $notification_tables = array(
            _DB_PREFIX_.'oyst_payment_notification',
            _DB_PREFIX_.'oyst_api_order',
        );

        $payment_notification_url = $this->context->link->getModuleLink(
            'oyst',
            'paymentNotification'
        );
        $notification_bo_url = $this->context->link->getModuleLink(
            'oyst',
            'notification'
        );
        $hash_key = Configuration::get('FC_OYST_HASH_KEY');
        $name_module = $this->module->name;
        $params_conf = '&configure='.$name_module.'&tab_module='.$this->module->tab.'&module_name='.$name_module;
        $custom_success_error = Configuration::get('FC_OYST_REDIRECT_SUCCESS_CUSTOM');
        $custom_error_error = Configuration::get('FC_OYST_REDIRECT_ERROR_CUSTOM');
        $custom_conf_error = Configuration::get('FC_OYST_OC_REDIRECT_CONF_CUSTOM');

        $assign['logsFile'] = $filesName;
        $assign['hasApiKey']     = $hasApiKey;
        $assign['module_dir']    = $this->path;
        $assign['message']       = '';
        $assign['phone']         = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $assign['apikey_prod_test_error_freepay']     = '';
        $assign['apikey_sandbox_test_error_freepay']  = '';
        $assign['apikey_custom_test_error_freepay']   = '';
        $assign['apikey_prod_test_error_oneclick']    = '';
        $assign['apikey_sandbox_test_error_oneclick'] = '';
        $assign['apikey_custom_test_error_oneclick']  = '';
        $assign['result']                   = $this->configuration_result;
        $assign['ps_version']               = Tools::substr(_PS_VERSION_, 0, 3);
        $assign['module_version']           = $this->module->version;
        $assign['oyst_library_version']     = OystApiClientFactory::getVersion();
        $assign['allow_url_fopen_check']    = ini_get('allow_url_fopen');
        $assign['curl_check']               = function_exists('curl_version');
        $assign['payment_notification_url'] = $payment_notification_url.'?key='.$hash_key;
        $assign['notification_url']         = $notification_bo_url.'?key='.$hash_key;
        $assign['notification_bo_url']      = '/modules/oyst/notification-bo.php?key='.$hash_key;
        $assign['configureLink']            = $this->context->link->getAdminLink('AdminModules', true).$params_conf;
        $assign['redirect_success_urls']    = $this->redirect_success_urls;
        $assign['redirect_error_urls']      = $this->redirect_error_urls;
        $assign['redirect_oc_conf_urls']    = $this->redirect_oc_conf_urls;
        $assign['custom_success_error']     = !Validate::isAbsoluteUrl($custom_success_error);
        $assign['custom_error_error']       = !Validate::isAbsoluteUrl($custom_error_error);
        $assign['custom_conf_error']        = !Validate::isAbsoluteUrl($custom_conf_error);
        $assign['carrier_list']             = $this->getCarrierList();
        $assign['type_list']                = $shipmentTypes;
        $assign['shipment_default']         = (int)Configuration::get('FC_OYST_SHIPMENT_DEFAULT');
        $assign['order_state']              = OrderState::getOrderStates($id_lang);
        $assign['languages']                = Language::getLanguages(false);
        $assign['restriction_languages']    = $restriction_languages;
        $assign['notification_tables']      = $notification_tables;
        $assign['currentOneClickApiKeyValid'] = $isCurrentOneClickApiKeyValid && count($shipmentTypes);
        $assign['current_tab'] = Tools::getValue('current_tab') ?: '#tab-content-FreePay';
        $assign['my_ip'] = Tools::getRemoteAddr();

        $clientPhone = Configuration::get('FC_OYST_MERCHANT_PHONE');
        $isGuest     = Configuration::get('FC_OYST_GUEST');

        if ($isGuest && $clientPhone) {
            $this->showMessageToMerchant($assign);
        }

        if ($hasApiKey && (!empty($currentFreePayApiKey) && !$isCurrentFreePayApiKeyValid || !empty($currentOneClickApiKey) && !$isCurrentOneClickApiKeyValid)) {
            $envFreePay = Tools::strtolower($this->module->getFreePayEnvironment());
            $envOneClick = Tools::strtolower($this->module->getOneClickEnvironment());

            if (!$isCurrentFreePayApiKeyValid) {
                $key = 'apikey_'.$envFreePay.'_test_error_freepay';

                if (array_key_exists($key, $assign)) {
                    $assign[$key] = !$isCurrentFreePayApiKeyValid;
                }
            }

            if (!$isCurrentOneClickApiKeyValid) {
                $key = 'apikey_'.$envOneClick.'_test_error_oneclick';

                if (array_key_exists($key, $assign)) {
                    $assign[$key] = !$isCurrentOneClickApiKeyValid;
                }
            }

            // First time merchant enter a key after submitting the contact form
            if ($isGuest) {
                Configuration::updateValue('FC_OYST_GUEST', false);
            }
        }

        $lists_conf  = array_keys($this->configurations);

        foreach ($lists_conf as $conf) {
            $assign[$conf] = Configuration::get($conf);
        }

        $this->smarty->assign($this->module->name, $assign);

        $template = $goToForm || $hasError ? 'getGuestConfigure.tpl' : 'getMerchantConfigure.tpl';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCSS(array(
                $this->path.'views/css/freepay-1.5.css',
            ));
            $this->context->controller->addJS(array(
                $this->path.'views/js/bootstrapTab-1.5.js',
            ));
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->context->controller->addCSS(array(
                $this->path.'views/css/freepay-1.6.css',
            ));
        }

        $this->context->controller->addCSS(array(
            $this->path.'views/css/datatables/datatables.min.css',
        ));

        $this->context->controller->addJS(array(
            $this->path.'views/js/handleAdvancedConf.js',
            $this->path.'views/js/handleShipment.js',
            $this->path.'views/js/logManagement.js',
            $this->path.'views/js/datatables/datatables.min.js',
        ));

        // Check for 1.5 ??
        $this->context->controller->addJqueryPlugin('colorpicker');

        return $this->module->fcdisplay(__FILE__, $template);
    }

    private function postRequest()
    {
        if (Tools::isSubmit('action') && Tools::getValue('action') == 'getLog') {
            $logManager = new LoggerManager();
            echo $logManager->getContent(Tools::getValue('file'));
            die();
        }

        if (Tools::isSubmit('deleteLogs')) {
            $logManager = new LoggerManager();
            $logManager->deleteAll();
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
        $assign['form_get_apikey_transac'] = '';
        $assign['form_get_apikey_name_error']  = '';
        $assign['form_get_apikey_phone_error'] = '';
        $assign['form_get_apikey_email_error'] = '';
        $assign['form_get_apikey_transac_error'] = '';

        // Merchant filled the contact form
        if (!Tools::isSubmit('form_get_apikey_submit')) {
            return;
        }

        $goToForm = false;

        $name      = Tools::getValue('form_get_apikey_name');
        $phone     = Tools::getValue('form_get_apikey_phone');
        $email     = Tools::getValue('form_get_apikey_email');
        $nbTransac = Tools::getValue('form_get_apikey_transac');

        $assign['form_get_apikey_name']    = $name;
        $assign['form_get_apikey_phone']   = $phone;
        $assign['form_get_apikey_email']   = $email;
        $assign['form_get_apikey_transac'] = $nbTransac;

        $nameError      = '';
        $phoneError     = '';
        $emailError     = '';
        $nbTransacError = '';

        if (!$name || !Validate::isName($name)) {
            $hasError  = true;
            $nameError = $this->module->l('Please enter a valid name', 'oysthookgetconfigurationprocessor');
        }

        if (!$phone || !Validate::isPhoneNumber($phone)) {
            $hasError   = true;
            $phoneError = $this->module->l(
                'Please enter your phone number in the format 06 00 00 00 00',
                'oysthookgetconfigurationprocessor'
            );
        }

        if (!$email || !Validate::isEmail($email)) {
            $hasError   = true;
            $emailError = $this->module->l('Please enter a valid email', 'oysthookgetconfigurationprocessor');
        }

        if (!$nbTransac || !Validate::isInt($nbTransac)) {
            $hasError       = true;
            $nbTransacError = $this->module->l('Please enter a valid number', 'oysthookgetconfigurationprocessor');
        }

        $assign['form_get_apikey_name_error']    = $nameError;
        $assign['form_get_apikey_phone_error']   = $phoneError;
        $assign['form_get_apikey_email_error']   = $emailError;
        $assign['form_get_apikey_transac_error'] = $nbTransacError;
        $assign['form_get_apikey_notify_error']  = false;

        if (!$hasError) {
            $isSent = OystSDK::notify($name, $phone, $email, $nbTransac);

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
        $showSubMessage  = true;
        $message = $this->module->l(
            'A FreePay customer advisor shall contact you on',
            'oysthookgetconfigurationprocessor'
        );

        if (in_array($dayOfTheWeek, array(0, 1, 2, 3, 4)) && $currentDateTime > '2000') {
            // Sunday or Monday, Tuesday, Wednesday, Thursday after 8pm
            $showSubMessage = false;
            $message = $this->module->l(
                'A FreePay customer advisor shall contact you from tomorrow morning 8:30 am on',
                'oysthookgetconfigurationprocessor'
            );
        } elseif (in_array($dayOfTheWeek, array(1, 2, 3, 4, 5))
            && $currentDateTime > '0000' && $currentDateTime < '0830') {
            // Monday, Tuesday, Wednesday, Thursday, Friday between 12:01 am and 8:30 am
            $showSubMessage = false;
            $message = $this->module->l(
                'A FreePay customer advisor shall contact you this morning from 8:30 am on',
                'oysthookgetconfigurationprocessor'
            );
        } elseif ($dayOfTheWeek == 5 && $currentDateTime > '1800' || $dayOfTheWeek == 6) {
            // Friday after 6 pm and Saturday
            $showSubMessage = false;
            $message = $this->module->l(
                'A FreePay customer advisor shall contact you monday morning from 8:30 am on',
                'oysthookgetconfigurationprocessor'
            );
        } elseif (in_array($dayOfTheWeek, array(1, 2, 3, 4, 5)) && $currentDateTime > '1200' && $currentDateTime < '1400') {// Monday, Tuesday, Wednesday, Thursday, Friday entre 12h et 14h
            $showSubMessage = false;
            $message = $this->module->l(
                'A FreePay customer advisor shall contact you this afternoon from 14:30 pm on',
                'oysthookgetconfigurationprocessor'
            );
        }

        $assign['message'] = $message;
        $assign['show_sub_message'] = $showSubMessage;
    }

    private function getCarrierList()
    {
        $carrier_list = Carrier::getCarriers(
            $this->context->language->id,
            true,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );

        return $carrier_list;
    }
}
