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
        <div class="oyst-header-notification oyst-info">{l s='Oyst export is still running'}. <b>{$exportedProducts|count}</b> {l s='products have been exported'} <img src="../modules/oyst/views/img/balls.svg"> </div>
    {else}
        <div class="oyst-header-notification oyst-info">
            {l s='Oyst export is over'}
            <a href="#" class="close">X Close</a>
        </div>

        <script type="text/javascript">

            {literal}
            $('a.close').on('click', function () {
                $(this).parent().hide();

                $.post('../modules/oyst/notification-bo.php', {action: 'hideExportInfo'}, function (data) {
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
