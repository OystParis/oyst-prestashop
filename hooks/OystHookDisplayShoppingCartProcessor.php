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

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayShoppingCartProcessor extends FroggyHookProcessor
{
    public function run()
    {
        // Check allow currency
        $id_currency = $this->context->currency->id;
        $oyst_currencies = Configuration::get('FC_OYST_CURRENCIES');
        if ($oyst_currencies != null || $oyst_currencies != '') {
            if (false  !== strpos($oyst_currencies, ',')) {
                $currencies = explode(',', $oyst_currencies);
                $restriction_currencies = in_array($id_currency, $currencies)? true : false;
            } else {
                $restriction_currencies = $id_currency == $oyst_currencies ? true : false;
            }
        } else {
            $restriction_currencies = true;
        }

        // Check allow language
        $id_lang = $this->context->language->id;
        $oyst_languages = Configuration::get('FC_OYST_LANG');
        if ($oyst_languages != null || $oyst_languages != '') {
            if (false  !== strpos($oyst_languages, ',')) {
                $languages = explode(',', $oyst_languages);
                $restriction_languages = in_array($id_lang, $languages)? true : false;
            } else {
                $restriction_languages = $id_lang == $oyst_languages ? true : false;
            }
        } else {
            $restriction_languages = true;
        }

        $this->smarty->assign(array(
            'secureKey' => Configuration::get('FC_OYST_HASH_KEY'),
            'shopUrl' => trim(Tools::getShopDomainSsl(true).__PS_BASE_URI__, '/'),
            'stockManagement' => Configuration::get('PS_STOCK_MANAGEMENT'),
            'oneClickActivated' => (int)Configuration::get('OYST_ONE_CLICK_FEATURE_STATE'),
            'smartBtn' => Configuration::get('FC_OYST_SMART_BTN'),
            'borderBtn' => Configuration::get('FC_OYST_BORDER_BTN'),
            'themeBtn' => Configuration::get('FC_OYST_THEME_BTN'),
            'colorBtn' => Configuration::get('FC_OYST_COLOR_BTN'),
            'widthBtn' => Configuration::get('FC_OYST_WIDTH_BTN'),
            'heightBtn' => Configuration::get('FC_OYST_HEIGHT_BTN'),
            'restriction_currencies' => $restriction_currencies,
            'restriction_languages' => $restriction_languages,
            'shouldAsStock' => Configuration::get('FC_OYST_SHOULD_AS_STOCK'),
            'oyst_error' => $this->module->l('There isn\'t enough product in stock.'),
            'controller' => Context::getContext()->controller->php_self
        ));

        if (_PS_VERSION_ >= '1.6.0.0') {
            $this->context->controller->addJS(array(
                $this->path.'views/js/OystOneClickCart.js',
                trim($this->module->getOneClickUrl(), '/').'/1click/script/script.min.js',
            ));
        } else {
            $this->smarty->assign(array(
                'JSOystOneClickCart' => $this->path.'views/js/OystOneClickCart.js',
                'JSOneClickUrl' => trim($this->module->getOneClickUrl(), '/').'/1click/script/script.min.js',
            ));
        }

        $this->context->controller->addCSS(array(
            $this->path.'views/css/oyst.css',
        ));

        return $this->module->fcdisplay(__FILE__, 'displayShoppingCart.tpl');
    }
}
