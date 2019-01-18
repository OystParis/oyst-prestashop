<div id="oyst-confirmation">
    <p class="conf">
        {l s='Your order %s is complete.' sprintf=$oyst.order_reference mod='oyst'}<br>
        {l s='Payment transaction ID: %s' sprintf=$oyst.transaction_id mod='oyst'}<br><br>
        {l s='If you have questions, comments or concerns, please contact our' mod='oyst'} <a style="color:white" href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team' mod='oyst'}</a>.
    </p>
</div>
