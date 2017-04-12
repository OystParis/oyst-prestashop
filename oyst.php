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

require_once __DIR__ . '/autoload.php';

/**
 * Class Oyst
 */
class Oyst extends FroggyPaymentModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oyst';
        $this->version = '1.2.0';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->author = 'Oyst';
        $this->displayName = $this->l('Freepay');
        $this->description = $this->l('FreePay est une solution de paiement en ligne "full service" entièrement gratuite : 0% de commission, 0€ de frais d\'installation, 0€ d\'abonnement. Avec FreePay, éliminez vos coûts de transactions, augmentez vos marges.');
        $this->module_key = 'b79be2b346400227a9c886c9239470e4';

        // Set Oyst version as define
        if (!defined('_PS_OYST_VERSION_')) {
            define('_PS_OYST_VERSION_', $this->version);
        }

        // If PS 1.6 or greater, we enable bootstrap
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $this->bootstrap = true;
        }

        // If old configuration variable exists, we migrate it
        if (Configuration::get('FC_OYST_API_PAYMENT_KEY') != '') {
            Configuration::updateValue('FC_OYST_API_KEY', Configuration::get('FC_OYST_API_PAYMENT_KEY'));
            Configuration::deleteByName('FC_OYST_API_PAYMENT_KEY');
        }

        // If old configuration variable exists, we migrate it
        if (Configuration::get('FC_OYST_API_CATALOG_KEY') != '') {
            Configuration::updateValue('FC_OYST_API_KEY', Configuration::get('FC_OYST_API_CATALOG_KEY'));
            Configuration::deleteByName('FC_OYST_API_CATALOG_KEY');
        }
    }

    public function uninstall()
    {
        $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), $this,_DB_PREFIX_);
        $oystDb->uninstall();

        return parent::uninstall();
    }

    public function install()
    {
        $result = parent::install();

        // Clear cache
        Cache::clean('Module::getModuleIdByName_oyst');

        // Set Oyst in first position
        $id_hook = Hook::getIdByName('displayPayment');
        $id_module = Module::getModuleIdByName('oyst');
        $module = Module::getInstanceById($id_module);
        if (Validate::isLoadedObject($module)) {
            Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'hook_module` SET `position`= position + 1
            WHERE `id_hook` = '.(int)$id_hook);
            Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'hook_module` SET `position`= 1
            WHERE `id_hook` = '.(int)$id_hook.' AND `id_module` = '.$id_module);
        }

        if ($this->getApiKey() != '') {
            Configuration::updateValue('FC_OYST_GUEST', false);
        }

        $oystDb = new \Oyst\Service\InstallManager(Db::getInstance(), $this,_DB_PREFIX_);
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
        return $this->hookGetConfiguration();
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
            $data_json = Tools::jsonEncode($data);
            $data = var_export($data, true);
        }
        file_put_contents(dirname(__FILE__).'/logs/log-notification.txt', '['.date('Y-m-d H:i:s').'] '.$data_json."\n", FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/logs/log-notification.txt', '['.date('Y-m-d H:i:s').'] '.$data."\n", FILE_APPEND);
    }

    /**
     * @return DateTime|null
     */
    public function getRequestedCatalogDate()
    {
        $dataRegistered = Configuration::get('OYST_REQUESTED_CATALOG_DATE');
        $date = $dataRegistered ? new DateTime($dataRegistered) : null;

        return $date;
    }

    /**
     * @return bool
     */
    public function isCatalogExportStillRunning()
    {
        return (bool) Configuration::get(Oyst\Service\Configuration::CATALOG_EXPORT_STATE);
    }

    /**
     * @param $state
     * @return $this
     */
    public function setAdminPanelInformationVisibility($state)
    {
        // TIPS: Maybe better to have an AdminClass / Configuration to handle anything about this
        $state = (bool) $state ?
            Oyst\Service\Configuration::DISPLAY_ADMIN_INFO_ENABLE :
            Oyst\Service\Configuration::DISPLAY_ADMIN_INFO_DISABLE
        ;
        Configuration::updateValue(Oyst\Service\Configuration::DISPLAY_ADMIN_INFO_STATE, $state);

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminPanelInformationVisibility()
    {
        return (bool) Configuration::get(Oyst\Service\Configuration::DISPLAY_ADMIN_INFO_STATE);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        $env = strtoupper($this->getEnvironment());
        $key = $env == \Oyst\Service\Configuration::API_ENV_PREPROD ?
            \Oyst\Service\Configuration::API_KEY_PREPROD :
            \Oyst\Service\Configuration::API_KEY_PROD
        ;
        return Configuration::get($key);
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return Configuration::get(\Oyst\Service\Configuration::API_ENV);
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return 'PrestaShop-'.$this->version;
    }
}
