{**
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
 *}
{if $oneClickActivated  && $btnOneClickState && $restriction_currencies && $restriction_languages && $enabledBtn}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script src="{$JSOystOneClick|escape:'html':'UTF-8'}"></script>
        <script type="text/javascript">
            $( document).ready(function () {
                {if $displayBtnCart}
                    var oyst = new OystOneClickCart({$oneClickUrl|json_encode}, "{$controller|escape:'html':'UTF-8'}");
                    oyst.setLabelCta("{$oyst_label_cta|escape:'html':'UTF-8'}");
                {else}
                    var oyst = new OystOneClick({$oneClickUrl|json_encode}, {$product->id|json_encode}, "{$controller|escape:'html':'UTF-8'}");
                    oyst.setExportedCombinations({$synchronizedCombination|json_encode});
                    oyst.setAllowOosp({$allowOosp|intval});
                    oyst.setProductQuantity({$productQuantity|intval});
                    oyst.setStockManagement({$stockManagement|intval});
                    oyst.setErrorQuantityNullText("{$error_quantity_null_text|escape:'html':'UTF-8'}");
                    oyst.setErrorProductOutofstockText("{$error_product_outofstock_text|escape:'html':'UTF-8'}");
                {/if}
                oyst.setIdBtnAddToCart("{$idBtnAddToCart|escape:'html':'UTF-8'}");
                oyst.setIdSmartBtn("{$idSmartBtn|escape:'html':'UTF-8'}");
                oyst.setSmartBtn({$smartBtn|intval});
                oyst.setBorderBtn({$borderBtn|intval});
                oyst.setThemeBtn("{$themeBtn|escape:'html':'UTF-8'}");
                oyst.setColorBtn("{$colorBtn|escape:'html':'UTF-8'}");
                oyst.setWidthBtn("{$widthBtn|escape:'html':'UTF-8'}");
                oyst.setHeightBtn("{$heightBtn|escape:'html':'UTF-8'}");
                oyst.setMarginTopBtn("{$marginTopBtn|escape:'html':'UTF-8'}");
                oyst.setMarginLeftBtn("{$marginLeftBtn|escape:'html':'UTF-8'}");
                oyst.setMarginRightBtn("{$marginRightBtn|escape:'html':'UTF-8'}");
                oyst.setPositionBtn("{$positionBtn|escape:'html':'UTF-8'}");
                oyst.setOneClickModalUrl("{$oneClickModalUrl|escape:'html':'UTF-8'}");
                oyst.setSticky({$sticky|intval});
                oyst.prepareButton();

                var allowOystRedirectSelf = true;

                window.addEventListener('message', function (event) {
                    if (event.data.type == 'ORDER_REQUEST') {
                        oyst.setPreload(0);
                    }

                    if (event.data.type == "ORDER_COMPLETE"
                    || event.data.type == "ORDER_CONVERSION") {
                        allowOystRedirectSelf = false;
                    }

                    if (event.data.type == "ORDER_CANCEL") {
                        allowOystRedirectSelf = true;
                    }

                    if (event.data.type == "MODAL_CLOSE" && allowOystRedirectSelf) {
                    window.location.reload(false);
                    }
                });

                window.__OYST__ = window.__OYST__ || {};
                window.__OYST__.getOneClickURL = function(callback) {
                    oyst.requestOneCLick(callback);
                };
            });
        </script>
        <script src="{$JSOneClickUrl|escape:'html':'UTF-8'}"></script>
    {/if}
    {if $styles_custom}
        <style>
            {$styles_custom|escape:'html':'UTF-8'}
        </style>
    {/if}
{/if}
