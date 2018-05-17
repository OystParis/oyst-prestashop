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

require_once dirname(__FILE__) . '/autoload.php';

/**
 * Class Oyst
 */
class Oyst extends PaymentModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oyst';
        $this->version = '2.0.0';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->author = 'Oyst';
        $this->displayName = $this->l('Oyst - FreePay and 1Click');
        $this->description = $this->l('FreePay is a full service online payment solution entirely free: 0% commission, 0% installation fee, 0% subscription. With FreePay, eliminate your transaction costs, increase your margins.');
        $this->module_key = '728233ba4101873905adb6b9ec29f28f';
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        // Set Oyst version as define
        if (!defined('_PS_OYST_VERSION_')) {
            define('_PS_OYST_VERSION_', $this->version);
        }

        // If PS 1.6 or greater, we enable bootstrap
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $this->bootstrap = true;
        }
    }

//    public function __call($method, $args)
//    {
//        //List of concerned hooks by the ip restriction
//        $filtered_hooks = array(
//            'displayFooterProduct',
//            'displayHeader',
//            'displayPayment',
//            'displayShoppingCart'
//        );
//        foreach ($filtered_hooks as $filtered_hook) {
//            if (strpos($method, $filtered_hook) !== false) {
//                $ip = Tools::getRemoteAddr();
//                if (!empty($ip) && Configuration::hasKey('FC_OYST_ONLY_FOR_IP')) {
//                    $authorized_ips = Configuration::get('FC_OYST_ONLY_FOR_IP');
//                    if (!empty($authorized_ips) && strpos($authorized_ips, $ip) === false) {
//                        return null;
//                    }
//                }
//            }
//        }
//        return parent::__call($method, $args);
//    }

    public function uninstall()
    {
        $oystDb = new \Oyst\Classes\InstallManager(Db::getInstance(), $this);
        $oystDb->uninstall();

        return parent::uninstall();
    }

    public function install()
    {
        $result = parent::install();

        $result &= $this->registerHook('header');
        $result &= $this->registerHook('displayPaymentReturn');
        $result &= $this->registerHook('adminProductsExtra');

        // Clear cache
        Cache::clean('Module::getModuleIdByName_oyst');

        $result &= $this->installOrderStates();
        $result &= $this->updateConstants();

        $oystDb = new \Oyst\Classes\InstallManager(Db::getInstance(), $this);
        $result &= $oystDb->install();
        return $result;
    }

    /**
     * @return bool
     */
    public function installOrderStates()
    {
        $result = true;
        $langId = Configuration::get('PS_LANG_DEFAULT');
        $orderState = new OrderState(Configuration::get('OYST_STATUS_CANCELLATION_PENDING'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Annulation en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_CANCELLATION_PENDING', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_REFUND_PENDING'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_REFUND_PENDING', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND_PEND'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement partiel en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_PARTIAL_REFUND_PEND', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement partiel',
            );
            $orderState->color = '#FF7F50';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_PARTIAL_REFUND', $orderState->id);
        }
        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD_CHECK'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'En attente de vérification fraude par Oyst',
            );
            $orderState->color = '#FF8C00';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_FRAUD_CHECK', $orderState->id);
        }
        $orderState = new OrderState(Configuration::get('OYST_STATUS_WAIT_PAYMENT'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'En attente de paiement chez Oyst',
            );
            $orderState->color = '#360088';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_WAIT_PAYMENT', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Paiement frauduleux - NE PAS EXPEDIER',
            );
            $orderState->color = '#980000';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_FRAUD', $orderState->id);
        }

        return $result;
    }

    public function updateConstants()
    {
        // Params FreePay
        Configuration::updateValue('FC_OYST_ACTIVE_FRAUD', 0);
        Configuration::updateValue('FC_OYST_STATE_PAYMENT_FREEPAY', 2);

        // Params 1-Click Global
        Configuration::updateValue('FC_OYST_STATE_PAYMENT_ONECLICK', 2);
        Configuration::updateValue('FC_OYST_BTN_PRODUCT', 1);
        Configuration::updateValue('FC_OYST_SMART_BTN', 1);
        Configuration::updateValue('FC_OYST_BORDER_BTN', 1);
        Configuration::updateValue('FC_OYST_COLOR_BTN', '#E91E63');
        // Params 1-Click btn cart
        Configuration::updateValue('FC_OYST_ID_BTN_CART', '.standard-checkout');
        // Params 1-Click advanced
        Configuration::updateValue('FC_OYST_DELAY', 15);
        Configuration::updateValue('FC_OYST_MANAGE_QUANTITY', 1);
        Configuration::updateValue('FC_OYST_SHOULD_AS_STOCK', 1);
        Configuration::updateValue('FC_OYST_MANAGE_QUANTITY_CART', 0);
        Configuration::updateValue('FC_OYST_ONLY_FOR_IP', "");
        // Params 1-Click restrictions
        Configuration::updateValue('FC_OYST_CURRENCIES', Currency::getIdByIsoCode('EUR'));
        Configuration::updateValue('FC_OYST_LANG', Language::getIdByIso('FR'));

        //Generate API key if not exists
        if (!Configuration::hasKey('FC_OYST_API_KEY')) {
            Configuration::updateValue('FC_OYST_API_KEY', Tools::passwdGen(32));
        }
    }

    public function loadSQLFile($sql_file)
    {
        // Get install SQL file content
        $sql_content = Tools::file_get_contents($sql_file);

        // Replace prefix and store SQL command in array
        $sql_content = str_replace('@PREFIX@', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        // Execute each SQL statement
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }

        // Return result
        return $result;
    }

    /**
     * Configuration method
     * @return string $html
     */
    public function getContent()
    {
        return '';
    }

    /**
     * Logging methods
     */

    public function logNotification($name, $debug)
    {
        $data = "<!---- Start notification ".$name." -->\n";
        $data .= "Response:\n".var_export(Tools::file_get_contents('php://input'), true)."/n";
        $data .= "Debug:\n".var_export($debug, true)."/n";
        $data .= "<!---- End notification -->\n";
        $this->log($data);
    }

    public function log($data)
    {
        if (_PS_OYST_DEBUG_ != 1) {
            return '';
        }
        if (is_array($data)) {
            $data_json = json_encode($data);
            $data = var_export($data, true);
        }
        file_put_contents(
            dirname(__FILE__).'/logs/log-notification.txt',
            '['.date('Y-m-d H:i:s').'] '.$data_json."\n",
            FILE_APPEND
        );
        file_put_contents(
            dirname(__FILE__).'/logs/log-notification.txt',
            '['.date('Y-m-d H:i:s').'] '.$data."\n",
            FILE_APPEND
        );
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    public function hookHeader($params)
    {
        //TODO Condition on IP + if product page and 1-click enable/disable
        if (Configuration::hasKey('FC_OYST_SCRIPT_TAG_URL')) {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS(Configuration::get('FC_OYST_SCRIPT_TAG_URL'));
            } else {
                $this->context->controller->registerJavascript('modules-oyst', Configuration::get('FC_OYST_SCRIPT_TAG_URL'), ['position' => 'bottom', 'priority' => 150, 'server' => 'remote']);
            }
        }
    }
}
