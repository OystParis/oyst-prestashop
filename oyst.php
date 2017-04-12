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
        $this->version = '1.0.0';
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

        if (Configuration::get('FC_OYST_API_KEY') != '') {
            Configuration::updateValue('FC_OYST_GUEST', false);
        }

        Db::getInstance()->execute(file_get_contents(__DIR__.'/upgrade/sql/install-1.0.0.sql'));

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
     * Export catalog method
     */
    public function exportCatalog()
    {
        require_once _PS_MODULE_DIR_.'/oyst/controllers/cron/ExportCatalog.php';
        $controller = new OystExportCatalogModuleCronController($this);
        $controller->run();
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

    public function hookDisplayBackOfficeHeader()
    {
        if (!ModuleCore::isInstalled($this->name) || !ModuleCore::isEnabled($this->name)) {
            return '';
        }

        $content = '';

        $oystProductRepository = new ProductRepository(Db::getInstance());
        $exportedProducts = $oystProductRepository->getExportedProduct();

        /** @var Smarty_Internal_Template $template */
        $template = Context::getContext()->smarty->createTemplate(__DIR__.'/views/templates/hook/displayBackOfficeHeader.tpl');
        $exportDate = $this->getRequestedCatalogDate();
        $template->assign([
            'OYST_REQUESTED_CATALOG_DATE' => $exportDate ? $exportDate->format(Context::getContext()->language->date_format_full) : false,
            'OYST_HAS_EXPORT_REQUESTED' => $this->hasExportCatalogBeenRequested(),
            'OYST_IS_EXPORT_STILL_RUNNING' => $this->isCatalogExportStillRunning(),
            'exportedProducts' => $exportedProducts,
            'displayPanel' => $this->getAdminPanelInformationVisibility(),
        ]);

        $content .= $template->fetch();

        return $content;
    }

    /**
     * @return DateTime|null
     */
    public function getRequestedCatalogDate()
    {
        $date = null;
        if ($this->hasExportCatalogBeenRequested()) {
            $date = new DateTime(Configuration::get('OYST_REQUESTED_CATALOG_DATE'));
        }

        return $date;
    }

    /**
     * @return bool
     */
    public function isCatalogExportStillRunning()
    {
        return (bool) Configuration::get('OYST_IS_EXPORT_STILL_RUNNING');
    }

    /**
     * @param $state
     * @return $this
     */
    public function setIsExportCatalogRunning($state)
    {
        $state = ((bool) $state) ? 1 : 0;
        Configuration::updateValue('OYST_IS_EXPORT_STILL_RUNNING', $state);

        return $this;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setAdminPanelInformationVisibility($state)
    {
        // TIPS: Maybe better to have an AdminClass / Configuration to handle anything about this
        $state = (bool) $state ? 1 : 0;
        Configuration::updateValue('OYST_DISPLAY_ADMIN_INFO', $state);

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminPanelInformationVisibility()
    {
        return (bool) Configuration::get('OYST_DISPLAY_ADMIN_INFO');
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        $env = strtoupper($this->getEnvironment());
        return Configuration::get('FC_OYST_API_'.$env.'_KEY');
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return Configuration::get('FC_OYST_API_ENV');
    }

    /**
     * @param Product $product
     * @param Combination|null $combination
     * @return string
     */
    public function getProductReference(Product $product, Combination $combination = null)
    {
        return $product->id.(Validate::isLoadedObject($combination) ? '-'.$combination->id : '');
    }
}
