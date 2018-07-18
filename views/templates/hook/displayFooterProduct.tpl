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
{if $oneClickActivated && $btnOneClickState && $restriction_currencies && $restriction_languages && $enabledBtn}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script src="{$JSOystOneClick|escape:'html':'UTF-8'}"></script>
        <script src="{$JSOneClickUrl|escape:'html':'UTF-8'}"></script>
    {/if}
    <script type="text/javascript">
        $( document).ready(function(){
            var oyst = new OystOneClick({$shopUrl|cat:'/modules/oyst/oneClick.php?key='|cat:"$secureKey"|json_encode}, {$product->id|json_encode}, "{$controller|escape:'html':'UTF-8'}");
            oyst.setExportedCombinations({$synchronizedCombination|json_encode})
            oyst.setStockManagement({$stockManagement|intval});
            oyst.setAllowOosp({$allowOosp|intval});
            oyst.setProductQuantity({$productQuantity|intval});
            oyst.setSmartBtn({$smartBtn|intval});
            oyst.setBorderBtn({$borderBtn|intval});
            oyst.setIdSmartBtn("{$idSmartBtn|escape:'html':'UTF-8'}");
            oyst.setThemeBtn("{$themeBtn|escape:'html':'UTF-8'}");
            oyst.setColorBtn("{$colorBtn|escape:'html':'UTF-8'}");
            oyst.setWidthBtn("{$widthBtn|escape:'html':'UTF-8'}");
            oyst.setHeightBtn("{$heightBtn|escape:'html':'UTF-8'}");
            oyst.setMarginTopBtn("{$marginTopBtn|escape:'html':'UTF-8'}");
            oyst.setMarginLeftBtn("{$marginLeftBtn|escape:'html':'UTF-8'}");
            oyst.setMarginRightBtn("{$marginRightBtn|escape:'html':'UTF-8'}");
            oyst.setPositionBtn("{$positionBtn|escape:'html':'UTF-8'}");
            oyst.setIdBtnAddToCart("{$idBtnAddToCart|escape:'html':'UTF-8'}");
            oyst.setErrorQuantityNullText("{$error_quantity_null_text|escape:'html':'UTF-8'}");
            oyst.setErrorProductOutofstockText("{$error_product_outofstock_text|escape:'html':'UTF-8'}");
            oyst.setOneClickModalUrl("{$oneClickModalUrl|escape:'html':'UTF-8'}");
            oyst.setSticky({$sticky|intval});
            oyst.prepareButton();

            window.addEventListener('message', function (event) {
                if (event.data.type == 'ORDER_REQUEST') {
                    oyst.setPreload(0);
                }
            });

            window.__OYST__ = window.__OYST__ || {};
            window.__OYST__.getOneClickURL = function(callback) {
                oyst.requestOneCLick(callback);
            };
        });
    </script>
    {if $styles_custom}
        <style>
            {$styles_custom|escape:'html':'UTF-8'}
        </style>
    {/if}
{/if}
