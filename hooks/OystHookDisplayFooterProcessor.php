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

use Oyst;

/*
 * Security
 */
use Oyst\Repository\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookDisplayFooterProcessor extends FroggyHookProcessor
{
    public function run()
    {
        $controller = Context::getContext()->controller->php_self;
        $token = hash('sha256', Tools::jsonEncode(array(Configuration::get('FC_OYST_HASH_KEY'), _COOKIE_KEY_)));
        $assign = array();
        $oyst = new Oyst();

        if (!in_array($controller, $this->restrictionPage())) {
            return '';
        }

        // Get type btn with controller
        $suffix_conf = $this->getTypeBtn($controller);

        // Manage url for 1-Click
        $shopUrl = trim(Tools::getShopDomainSsl(true).__PS_BASE_URI__, '/');
        $oneClickUrl = $shopUrl.'/modules/oyst/oneClick.php?key='.$token;
        $JSOneClickUrl = trim($this->module->getOneClickUrl(), '/').'/1click/script/script.min.js';
        $assign['btnOneClickState'] = true;

        // Params specific for each page
        if ($controller != null && $oyst->displayBtnCart()) {
            $JSOystOneClick = $this->path.'views/js/OystOneClickCart.js';

            $assign['oyst_label_cta'] = $this->module->l(
                'Return shop.',
                'oysthookdisplayshoppingcartprocessor'
            );
        } else {
            $productRepository = new ProductRepository(Db::getInstance());
            $JSOystOneClick = $this->path.'views/js/OystOneClick.js';

            $product = new Product(Tools::getValue('id_product'));
            if (!Validate::isLoadedObject($product)) {
                return '';
            }

            $productCombinations = $product->getAttributeCombinations($this->context->language->id);

            $synchronizedCombination = array();
            foreach ($productCombinations as $combination) {
                    $stockAvailable = new StockAvailable(
                        StockAvailable::getStockAvailableIdByProductId(
                            $product->id,
                            $combination['id_product_attribute']
                        )
                    );
                    $synchronizedCombination[$combination['id_product_attribute']] = array(
                        'quantity' => $stockAvailable->quantity
                    );
            }

            //require for load Out Of Stock Information (isAvailableWhenOutOfStock)
            $product->loadStockData();

            $assign['btnOneClickState'] = $productRepository->getActive($product->id);
            $assign['product'] = $product;
            $assign['productQuantity'] = StockAvailable::getQuantityAvailableByProduct($product->id);
            $assign['synchronizedCombination'] = $synchronizedCombination;
            $assign['allowOosp'] = $product->isAvailableWhenOutOfStock((int)$product->out_of_stock);
            $assign['positionBtn'] = Configuration::get('FC_OYST_POSITION_BTN_PRODUCT');
            $assign['idSmartBtn'] = Configuration::get('FC_OYST_ID_SMART_BTN_PRODUCT');
            $assign['shouldAsStock'] = Configuration::get('FC_OYST_SHOULD_AS_STOCK');
        }

        // Params global
        $assign['secureKey'] = $token;
        $assign['oneClickUrl'] = $oneClickUrl;
        $assign['oneClickActivated'] = (int)Configuration::get('OYST_ONE_CLICK_FEATURE_STATE');
        $assign['enabledBtn'] = Configuration::get('FC_OYST_BTN_'.$suffix_conf);
        $assign['smartBtn'] = Configuration::get('FC_OYST_SMART_BTN');
        $assign['borderBtn'] = Configuration::get('FC_OYST_BORDER_BTN');
        $assign['themeBtn'] = Configuration::get('FC_OYST_THEME_BTN');
        $assign['colorBtn'] = Configuration::get('FC_OYST_COLOR_BTN');
        $assign['restriction_currencies'] = $this->restrictionsCurrencies();
        $assign['restriction_languages'] = $this->restrictionsLanguages();
        $assign['stockManagement'] = Configuration::get('PS_STOCK_MANAGEMENT');
        $assign['controller'] = $controller;
        $assign['styles_custom'] = $this->addButtonWrapperStyles();
        $assign['oyst_error'] = $this->module->l(
            'There isn\'t enough product in stock.',
            'oystHookdisplayfooterproductprocessor'
        );

        // Params custom global
        $assign['idBtnAddToCart'] = Configuration::get('FC_OYST_ID_BTN_'.$suffix_conf);
        $assign['widthBtn'] = Configuration::get('FC_OYST_WIDTH_BTN_'.$suffix_conf);
        $assign['heightBtn'] = Configuration::get('FC_OYST_HEIGHT_BTN_'.$suffix_conf);
        $assign['marginTopBtn'] = Configuration::get('FC_OYST_MARGIN_TOP_BTN_'.$suffix_conf);
        $assign['marginLeftBtn'] = Configuration::get('FC_OYST_MARGIN_LEFT_BTN_'.$suffix_conf);
        $assign['marginRightBtn'] = Configuration::get('FC_OYST_MARGIN_RIGHT_BTN_'.$suffix_conf);


        $this->smarty->assign($assign);

        if (_PS_VERSION_ >= '1.6.0.0') {
            $this->context->controller->addJS(array(
                $JSOystOneClick,
                $JSOneClickUrl,
            ));
        } else {
            $this->smarty->assign(array(
                'JSOystOneClick' => $JSOystOneClick,
                'JSOneClickUrl' => $JSOneClickUrl,
            ));
        }

        $this->context->controller->addCSS(array(
            $this->path.'views/css/oyst.css',
        ));

        return $this->module->fcdisplay(__FILE__, 'displayFooter.tpl');
    }

    public function addButtonWrapperStyles()
    {
        $styles = Configuration::get('FC_OYST_CUSTOM_CSS');

        if (!$styles && $styles != '') {
            return null;
        }

        $styles = rtrim($styles, " \n\r;");

        return $styles;
    }

    /**
     * @return bool
     */
    public function restrictionsCurrencies()
    {
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

        return $restriction_currencies;
    }

    /**
     * @return bool
     */
    public function restrictionsLanguages()
    {
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

        return $restriction_languages;
    }

    /**
     * @return array
     */
    public function restrictionPage()
    {
        $access_allow = array();

        if (Configuration::get('FC_OYST_BTN_CART')) {
            $access_allow[] = 'order';
        }

        if (Configuration::get('FC_OYST_BTN_PRODUCT')) {
            $access_allow[] = 'product';
        }

        if (Configuration::get('FC_OYST_BTN_LAYER')) {
            $access_allow[] = 'index';
            $access_allow[] = 'category';
        }

        return $access_allow;
    }

    /**
     * @return string
     */
    public function getTypeBtn($controller = 'product')
    {
        if ($controller == 'order') {
            $btn = 'CART';
        } elseif ($controller == 'index' || $controller == 'category') {
            $btn = 'LAYER';
        } else {
            $btn = 'PRODUCT';
        }

        return $btn;
    }
}
