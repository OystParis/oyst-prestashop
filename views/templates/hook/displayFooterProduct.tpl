<script type="text/javascript" src="{$shopUrl}/modules/oyst/views/js/OystOneClick.js"></script>
<script type="text/javascript" src="{$oneClickUrl}/1click/script/script.min.js"></script>

<script type="text/javascript">
    const oyst = new OystOneClick({$shopUrl|cat:'/modules/oyst/oneClick.php'|json_encode}, {$product->id|json_encode});
    oyst.prepareButton();

    window.__OYST__ = window.__OYST__ || {};
    window.__OYST__.getOneClickURL = function(callback) {
        oyst.requestOneCLick(callback);
    };
</script>

<style type="text/css">
    #oyst-1click-button {
        margin: 10px !important;
    }
</style>
