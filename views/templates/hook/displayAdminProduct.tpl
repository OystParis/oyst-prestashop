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
<script type="text/javascript">
    $(function () {
        $('#productOystSyncInfo').prependTo($('#content'));
    });
</script>
<style>
    #productOystSyncInfo {
        text-align: center;
        font-weight: bold;
        line-height: 30px;
    }

    #productOystSyncInfo.oyst.isSynced {
        background-color: #4e904e;
        color: whitesmoke;
    }

    #productOystSyncInfo.oyst.notSynced {
        background-color: #a01c02;
        color: whitesmoke;
    }
</style>

<div id="productOystSyncInfo" class="oyst {if $isProductSent}isSynced{else}notSynced{/if}">
    {if $isProductSent}
        The product is synchronized with Oyst
    {else}
        The product is not synchronized with Oyst
    {/if}
</div>
