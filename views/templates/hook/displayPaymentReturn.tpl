<div id="oyst-confirmation">
    <p style="background: #55C65E;
        border: 1px solid #3b9042;
        border-radius: 4px;
        padding: 10px;
        color: #fff;
        font-size: 12px;
        font-weight: bold;">
        {l s='Your order %s is complete.' sprintf=$oyst.order_reference mod='oyst'}<br>
        {l s='If you have questions, comments or concerns, please contact our' mod='oyst'} <a style="color:white" href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team' mod='oyst'}</a>.
    </p>

    <script>
        {literal}
        window.__OYST__ = window.__OYST__ || {};
        window.__OYST__.tracking = {$tracking_parameters};
        {/literal}
    </script>
</div>
