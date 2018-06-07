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

use Oyst\Classes\OystAPIKey;

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

        $oystDb = new \Oyst\Classes\InstallManager(Db::getInstance(), $this);
        $result &= $oystDb->install();
        return $result;
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
        return OystAPIKey::getAPIKey();
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
        //Only for test
        if (Tools::getIsset('show-context')) {
            echo "<pre>";
            print_r($this->context);
            echo "</pre>";
            exit;
        }
        if (Configuration::hasKey('OYST_SCRIPT_TAG_URL')) {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS(Configuration::get('OYST_SCRIPT_TAG_URL'));
                $this->context->controller->addJS('modules/'.$this->name.'/views/js/oyst.js');
            } else {
                $this->context->controller->registerJavascript('modules-oyst-supertag', Configuration::get('OYST_SCRIPT_TAG_URL'), ['position' => 'bottom', 'priority' => 150, 'server' => 'remote']);
                $this->context->controller->registerJavascript('modules-oyst', 'modules/'.$this->name.'/views/js/oyst.js', ['position' => 'bottom', 'priority' => 150]);
            }
        }
    }
}
