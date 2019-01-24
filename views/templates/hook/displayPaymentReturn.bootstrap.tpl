<div id="oyst-confirmation">
    <div class="alert alert-success">
        <i class="success-confirm-payment"></i> {l s='Your order %s is complete.' sprintf=$oyst.order_reference mod='oyst'}
    </div>
    <div class="alert" style="border: 1px solid #ccc;background: #f7f7f7">
        <p>{l s='If you have questions, comments or concerns, please contact our' mod='oyst'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team' mod='oyst'}</a>.</p>
    </div>
</div>


