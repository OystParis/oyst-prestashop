<script type="text/javascript" src="{$__PS_BASE_URI__}modules/oyst/views/js/OystOneClick.js"></script>
<script type="text/javascript" src="https://cdn.staging.oyst.eu/1click/script/script.min.js"></script>

<script type="text/javascript">
    const oyst = new OystOneClick({$oneClickUrl|json_encode}, {$productInfo.productId|json_encode});
    oyst.prepareButton();

    window.__OYST__ = window.__OYST__ || {};
    window.__OYST__.getOneClickURL = function(callback) {
        oyst.requestOneCLick(callback);
    };
</script>
{* Temporary waiting fix from staging*}
{*<script type="text/javascript" src="/modules/oyst/views/js/script.js"></script>*}
