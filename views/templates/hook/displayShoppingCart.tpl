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
{if $oneClickActivated && $restriction_currencies && $restriction_languages && $btnCart && $controller == 'order'}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script src="{$JSOystOneClickCart|escape:'html':'UTF-8'}"></script>
        <script src="{$JSOneClickUrl|escape:'html':'UTF-8'}"></script>
    {/if}
    <script type="text/javascript">
        $( document).ready(function(){
            var oyst = new OystOneClickCart({$shopUrl|cat:'/modules/oyst/oneClick.php?key='|cat:"$secureKey"|json_encode}, "{$controller|escape:'html':'UTF-8'}");
            oyst.setSmartBtn({$smartBtn|intval});
            oyst.setBorderBtn({$borderBtn|intval});
            oyst.setThemeBtn("{$themeBtn|escape:'html':'UTF-8'}");
            oyst.setColorBtn("{$colorBtn|escape:'html':'UTF-8'}");
            oyst.setWidthBtn("{$widthBtn|escape:'html':'UTF-8'}");
            oyst.setHeightBtn("{$heightBtn|escape:'html':'UTF-8'}");
            oyst.setMarginTopBtn("{$marginTopBtn|escape:'html':'UTF-8'}");
            oyst.setMarginLeftBtn("{$marginLeftBtn|escape:'html':'UTF-8'}");
            oyst.setMarginRightBtn("{$marginRightBtn|escape:'html':'UTF-8'}");
            oyst.setLabelCta("{$oyst_label_cta|escape:'html':'UTF-8'}");
            oyst.prepareButton();

            window.__OYST__ = window.__OYST__ || {};
            window.__OYST__.getOneClickURL = function(callback) {
                oyst.requestOneCLick(callback);
            };
        });
    </script>
    {if $styles_custom}
        <style>
            {$styles_custom}
        </style>
    {/if}
{/if}
