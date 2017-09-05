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
{if $oneClickActivated && $btnOneClickState}

    <script type="text/javascript">
        var oyst = new OystOneClick({$shopUrl|cat:'/modules/oyst/oneClick.php'|json_encode}, {$product->id|json_encode});
        oyst.setExportedCombinations({$synchronizedCombination|json_encode})
        oyst.setStockManagement({$stockManagement});
        oyst.setAllowOosp({$allowOosp});
        oyst.setProductQuantity({$productQuantity});
        oyst.prepareButton();

        window.__OYST__ = window.__OYST__ || {};
        window.__OYST__.getOneClickURL = function(callback) {
            oyst.requestOneCLick(callback);
        };
    </script>

    <style type="text/css">
        #oneClickContainer {
            margin: 10px !important;
        }
    </style>
{/if}
