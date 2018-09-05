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

use Oyst\Classes\OystAPIKey;

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
        $this->displayName = $this->l('Oyst - 1Click');
        $this->description = $this->l('Oyst is an online shopping solution allowing users to buy in 1-click on any website, from any page (not only any more through the traditional "cart" page).');
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

        $result &= $this->registerHook('footer');
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

    public function hookFooter($params)
    {
        if (Configuration::hasKey('OYST_SCRIPT_TAG') && Configuration::hasKey('OYST_MERCHANT_ID')) {
            $script_tag = str_replace('[MERCHANT_ID_PLACEHOLDER]', Configuration::get('OYST_MERCHANT_ID'), base64_decode(Configuration::get('OYST_SCRIPT_TAG')));
            $this->context->smarty->assign(array(
                'page_name_oyst' => $this->getPageName(),
                'base_url' => Tools::getShopDomainSsl(true),
                'cart_url' => $this->context->link->getPageLink('cart', Configuration::get('PS_SSL_ENABLED')),
                'redirect_url' => $this->context->link->getModuleLink('oyst', 'oneclickreturn', array(), Configuration::get('PS_SSL_ENABLED')),
                'script_tag' => $script_tag,
                'form_selector' => 'buy_block',
            ));
            return $this->display(__FILE__, './views/templates/hook/displayFooter.tpl');
        }
        return '';
    }

    private function getPageName()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            // Are we in a payment module
            $module_name = '';
            if (Validate::isModuleName(Tools::getValue('module'))) {
                $module_name = Tools::getValue('module');
            }

            if (!empty($this->context->controller->php_self)) {
                $page_name = $this->context->controller->php_self;
            } elseif (Tools::getValue('fc') == 'module' && $module_name != '' && (Module::getInstanceByName($module_name) instanceof PaymentModule)) {
                $page_name = 'module-payment-submit';
            }
            // @retrocompatibility Are we in a module ?
            elseif (preg_match('#^'.preg_quote($this->context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
                $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
            } else {
                $page_name = Dispatcher::getInstance()->getController();
                $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
            }
        } else {
            $page_name = $this->context->controller->getPageName();
        }
        return $page_name;
    }
}
