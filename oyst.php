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
 * @license GNU GENERAL PUBLIC LICENSE
 */

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

/*
 * Include Froggy Library
 */
if (!class_exists('FroggyModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyModule.php';
}
if (!class_exists('FroggyPaymentModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyPaymentModule.php';
}

class Oyst extends FroggyPaymentModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oyst';
        $this->version = '0.2.0';
        $this->author = 'Froggy Commerce / 23Prod';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->displayName = $this->l('Oyst');
        $this->description = $this->l('Oyst provides 1 click shopping advertising technology and creates a new ecosystem at the crossroads of customised advertising and online payment.');
        $this->module_key = '';
    }

    /**
     * Configuration method
     * @return string $html
     */
    public function getContent()
    {
        return $this->hookGetContent();
    }

    public function exportCatalog()
    {
        require_once _PS_MODULE_DIR_.'/oyst/controllers/cron/ExportCatalog.php';
        $controller = new OystExportCatalogModuleCronController($this);
        $controller->run();
    }
}
