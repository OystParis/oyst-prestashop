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
