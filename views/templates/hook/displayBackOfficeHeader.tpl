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
<style>
    .oyst-header-notification {
        text-align: center;
        font-weight: bold;
        line-height: 30px;
        {if $marginRequired}
        margin: 10px 0;
        {/if}
    }

    .oyst-header-notification > a.close {
        float: right;
        margin-right: 20px;
    }

    .oyst-header-notification.oyst-info {
        background-color: #0671a7;
        color: whitesmoke;
    }
</style>
{if $displayPanel}
    {if $OYST_IS_EXPORT_STILL_RUNNING}
        <div class="oyst-header-notification oyst-info">{l s='Oyst export is still running' mod='oyst'}. <b>{$exportedProducts|count}</b> {l s='products have been exported' mod='oyst'} <img src="../modules/oyst/views/img/balls.svg"> </div>
    {else}
        <div class="oyst-header-notification oyst-info">
            {l s='Oyst export is over' mod='oyst'}
            <a href="#" class="close">X Close</a>
        </div>

        <script type="text/javascript">
            var key = '{$secureKey}';
            {literal}
            $('a.close').on('click', function () {
                $(this).parent().hide();

                $.post('../modules/oyst/notification-bo.php?key='+key, {action: 'hideExportInfo'}, function (data) {
                    // Nothing needs to be done here.
                });
            });
            {/literal}
        </script>
    {/if}

    <script type="text/javascript">
        $(function () {
            $('.oyst-header-notification').prependTo($('#content'));
        });
    </script>

{/if}
