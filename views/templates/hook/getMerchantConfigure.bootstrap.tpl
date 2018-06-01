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
<link type="text/css" rel="stylesheet" href="{$oyst.module_dir|escape:'html':'UTF-8'}views/css/freepay-1.6.css" media="all">

<script>
    var notification_bo_url = "{$oyst.notification_bo_url|escape:'htmlall':'UTF-8'}";
    var module_dir = "{$oyst.module_dir|escape:'htmlall':'UTF-8'}";
    var my_ip = "{$oyst.my_ip|escape:'htmlall':'UTF-8'}";
    var state_oc = "{$oyst.OYST_ONE_CLICK_FEATURE_STATE}";
    var key_valid = "{$oyst.currentOneClickApiKeyValid}";
</script>

{if isset($oyst.result) && $oyst.result eq 'ok'}
<div class="bootstrap">
    <div class="alert alert-success">
        <button data-dismiss="alert" class="close" type="button">×</button>
        {l s='The new configuration has been saved!' mod='oyst'}
    </div>
</div>
{/if}

{if !$oyst.allow_url_fopen_check}
<div class="bootstrap">
    <div class="alert alert-danger">
        {l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}
    </div>
</div>
{/if}
{if !$oyst.curl_check}
<div class="bootstrap">
    <div class="alert alert-danger">
        {l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}
    </div>
</div>
{/if}

{if isset($apiError)}
<div class="bootstrap">
    <div class="alert alert-danger">
        <strong>{l s='Got an API error:' mod='oyst'}</strong> {$apiError|escape:'htmlall':'UTF-8'}
    </div>
</div>
{/if}

{if isset($oyst.shipment_default) && $oyst.shipment_default == 0}
<div class="bootstrap">
    <div class="alert alert-danger">
        <strong>{l s='Carrier default error:' mod='oyst'}</strong> {l s='Please select carrier default' mod='oyst'}
    </div>
</div>
{/if}

{if !$oyst.type_shipping_default || $oyst.type_shipping_default != 'home_delivery'}
<div class="bootstrap">
    <div class="alert alert-danger">
        <strong>{l s='Type carrier default error:' mod='oyst'}</strong> {l s='Please select carrier default with home_delivery' mod='oyst'}.
        {l s='Current value for carrier :' mod='oyst'} <strong>{l s='%s' sprintf=$oyst.name_type_shipping_default}</strong>
    </div>
</div>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
<form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="">
    <div align="center" style="font-size: 16px;">
        <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
        <p style="font-size: 12px">Lib v {$oyst.oyst_library_version|escape:'htmlall':'UTF-8'}</p>
    </div>
    {if $oyst.FC_OYST_GUEST && $oyst.phone}
    <div class="text-center">
        <p>{$oyst.message|escape:'html':'UTF-8'} <strong>{$oyst.phone|escape:'html':'UTF-8'}</strong>
            (<a href="{$oyst.configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8'}">{l s='edit' mod='oyst'}</a>)
        </p>

        {if $oyst.show_sub_message}
        <p><h2>{l s='Pick up your phone' mod='oyst'}</h2></p>
        {/if}
        <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/phone.gif" width="70"/></p>
        <p>
            {l s='Please, get these information ready:' mod='oyst'}<br>
            <strong>{l s='SIRET' mod='oyst'}</strong><br>
            <strong>{l s='VAT Number' mod='oyst'}</strong><br>
            <strong>{l s='IBAN' mod='oyst'}</strong>
        </p>
    </div>
    {/if}
    <div class="panel oyst_fieldset">
        <div>
            <ul id="oyst-config-menu" class="nav nav-tabs" role="tablist">
                <li role="presentation" class="{if $oyst.current_tab == '#tab-content-FreePay'}active{/if}"><a href="#tab-content-FreePay" role="tab" data-toggle="tab">{l s='FreePay' mod='oyst'}</a></li>
                <li role="presentation" class="{if $oyst.current_tab == '#tab-content-1-click'}active{/if}"><a href="#tab-content-1-click" role="tab" data-toggle="tab">{l s='1-click' mod='oyst'}</a></li>
            </ul>
            <div class="oyst-admin-tab tab-content">
                <input type="hidden" id="current_tab_value" name="current_tab" value="{$oyst.current_tab|escape:'htmlall':'UTF-8'}"/>
                <div role="tabpanel" class="tab-pane{if $oyst.current_tab == '#tab-content-FreePay'} active{/if}" id="tab-content-FreePay">
                    <fieldset>
                        <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Enable FreePay' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7" style="height: 31px;">
                                <input type="checkbox" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
                            </div>
                        </div>
                        <div class="form-group clearfix env prod" style="display: none;">
                            <label class="control-label col-md-3 col-lg-3">{l s='FreePay API Production Key' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <div class="input-group">
                                    <input type="text" id="OYST_API_PROD_KEY_FREEPAY" name="OYST_API_PROD_KEY_FREEPAY" value="{$oyst.OYST_API_PROD_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                    </span>
                                </div>
                                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                                {if $oyst.apikey_prod_test_error_freepay}
                                <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group clearfix env sandbox" style="display: none;">
                            <label class="control-label col-md-3 col-lg-3">{l s='FreePay API Sandbox Key' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <div class="input-group">
                                    <input type="text" id="OYST_API_SANDBOX_KEY_FREEPAY" name="OYST_API_SANDBOX_KEY_FREEPAY" value="{$oyst.OYST_API_SANDBOX_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                    </span>
                                </div>
                                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.sandbox.oyst.eu/signup" target="_blank">backoffice.sandbox.oyst.com</a></p>
                                <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                                {if $oyst.apikey_sandbox_test_error_freepay}
                                <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group clearfix env custom" style="display: none;">
                            <label class="control-label col-md-3 col-lg-3">{l s='FreePay API Custom Key' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <div class="input-group">
                                    <input type="text" id="OYST_API_CUSTOM_KEY_FREEPAY" name="OYST_API_CUSTOM_KEY_FREEPAY" value="{$oyst.OYST_API_CUSTOM_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                    </span>
                                </div>
                                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.staging.oyst.eu/signup" target="_blank">backoffice.staging.oyst.com</a></p>
                                <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                                {if $oyst.apikey_custom_test_error_freepay}
                                <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group clearfix urlCustomization">
                            <label class="control-label col-md-3 col-lg-3">{l s='Success Url' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <select id="FC_OYST_REDIRECT_SUCCESS" name="FC_OYST_REDIRECT_SUCCESS">
                                {foreach from=$oyst.redirect_success_urls key=url item=label}
                                    <option value="{$url|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_REDIRECT_SUCCESS == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label|escape:'html':'UTF-8'}</option>
                                {/foreach}
                                </select>
                                <input type="text" id="FC_OYST_REDIRECT_SUCCESS_CUSTOM" name="FC_OYST_REDIRECT_SUCCESS_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_SUCCESS_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                                {if $oyst.custom_success_error}
                                <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group clearfix urlCustomization">
                            <label class="control-label col-md-3 col-lg-3">{l s='Error Url' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <select id="FC_OYST_REDIRECT_ERROR" name="FC_OYST_REDIRECT_ERROR">
                                {foreach from=$oyst.redirect_error_urls key=url item=label}
                                    <option value="{$url|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_REDIRECT_ERROR == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label|escape:'html':'UTF-8'}</option>
                                {/foreach}
                                </select>
                                <input type="text" id="FC_OYST_REDIRECT_ERROR_CUSTOM" name="FC_OYST_REDIRECT_ERROR_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_ERROR_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                                {if $oyst.custom_error_error}
                                <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                                {/if}
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Environment' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <select name="OYST_API_ENV_FREEPAY">
                                    <option value="prod" {if $oyst.OYST_API_ENV_FREEPAY == 'prod'}selected="selected"{/if}>{l s='Production' mod='oyst'}</option>
                                    <option value="sandbox" {if $oyst.OYST_API_ENV_FREEPAY == 'sandbox'}selected="selected"{/if}>{l s='Sandbox' mod='oyst'}</option>
                                    <option value="custom" {if $oyst.OYST_API_ENV_FREEPAY == 'custom'}selected="selected"{/if}>{l s='Custom' mod='oyst'}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group clearfix env custom" style="display: none;">
                            <label class="control-label col-md-3 col-lg-3">{l s='Endpoint API Custom' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <input type="text" id="OYST_API_CUSTOM_ENDPOINT_FREEPAY" name="OYST_API_CUSTOM_ENDPOINT_FREEPAY" value="{$oyst.OYST_API_CUSTOM_ENDPOINT_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-lg-3">{l s='State payment' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <select id="FC_OYST_STATE_PAYMENT_FREEPAY" name="FC_OYST_STATE_PAYMENT_FREEPAY">
                                    {foreach from=$oyst.order_state item=state}
                                        <option value="{$state.id_order_state|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_STATE_PAYMENT_FREEPAY == $state.id_order_state} selected="selected"{/if}>{$state.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        {* Deprecated for version 1.11.0 *}
                        {* <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Create order before payment' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7" style="height: 31px;">
                                <input type="checkbox" id="FC_OYST_PREORDER_FEATURE" name="FC_OYST_PREORDER_FEATURE" value="1"{if $oyst.FC_OYST_PREORDER_FEATURE} checked="checked"{/if} />
                            </div>
                        </div> *}
                        <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Enable Fraudscoring' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <input type="checkbox" name="FC_OYST_ACTIVE_FRAUD" value="1"{if $oyst.FC_OYST_ACTIVE_FRAUD} checked="checked"{/if} />
                                <span class="help-block">{l s='Allows you to display in your back-office the potentially abnormal orders detected by our algorithm. The management of the fraud is assured by our teams independently of this option.' mod='oyst'}</span>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div role="tabpanel" class="panel tab-pane{if $oyst.current_tab == '#tab-content-1-click'} active{/if}" id="tab-content-1-click" style="border-top: 0;border-radius: initial;">
                    <div class="row">
                        <div class="col-lg-2 col-md-3">
                            <ul class="nav nav-pills nav-stacked" role="tablist">
                                <li role="presentation" class="active"><a role="tab" data-toggle="tab" href="#conf-oc">{l s='Configuration One-click' mod='oyst'}</a></li>
                                <li role="presentation" class=""><a role="tab" data-toggle="tab" href="#custom-btn-general">{l s='Customization of button on global' mod='oyst'}</a></li>
                                <li role="presentation" class=""><a role="tab" data-toggle="tab" href="#custom-btn">{l s='Button product' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn-cart"/>{l s='Button cart' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn-layer-cart"/>{l s='Button layer cart' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn-login"/>{l s='Button login' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn-payment"/>{l s='Button payment' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn-address"/>{l s='Button address' mod='oyst'}</a></li>
                                <li role="presentation" class=""><a role="tab" data-toggle="tab" href="#settings-carrier">{l s='Settings carrier' mod='oyst'}</a></li>
                                <li role="presentation" class=""><a role="tab" data-toggle="tab" href="#settings-advanced">{l s='Settings advanced' mod='oyst'}</a></li>
                                <li role="presentation" class=""><a role="tab" data-toggle="tab" href="#settings-restrictions">{l s='Settings restrictions' mod='oyst'}</a></li>
                                <li role="presentation" class="" id="tab-notification"><a role="tab" data-toggle="tab" href="#display-notifications">{l s='Notifications' mod='oyst'}</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-10 col-md-9 tab-content">
                            <div id="conf-oc" class="tab-pane active" role="tabpanel">
                                <div class="form-group clearfix">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Enable OneClick' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7" style="height: 31px;">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="OYST_ONE_CLICK_FEATURE_STATE" id="OYST_ONE_CLICK_FEATURE_STATE_ON" value="1" {if $oyst.OYST_ONE_CLICK_FEATURE_STATE == 1} checked="checked"{/if}>
                                            <label for="OYST_ONE_CLICK_FEATURE_STATE_ON" class="radioCheck">
                                                {l s='Yes' mod='oyst'}
                                            </label>
                                            <input type="radio" name="OYST_ONE_CLICK_FEATURE_STATE" id="OYST_ONE_CLICK_FEATURE_STATE_OFF" value="0" {if $oyst.OYST_ONE_CLICK_FEATURE_STATE == 0} checked="checked"{/if}>
                                            <label for="OYST_ONE_CLICK_FEATURE_STATE_OFF" class="radioCheck">
                                                {l s='No' mod='oyst'}
                                            </label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group clearfix env prod" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='1-Click API Production Key' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <div class="input-group">
                                            <input type="text" id="OYST_API_PROD_KEY_ONECLICK" name="OYST_API_PROD_KEY_ONECLICK" value="{$oyst.OYST_API_PROD_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                            </span>
                                        </div>
                                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                        {if $oyst.apikey_prod_test_error_oneclick}
                                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                        {/if}
                                    </div>
                                </div>
                                <div class="form-group clearfix env sandbox" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='1-Click API Sandbox Key' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <div class="input-group">
                                            <input type="text" id="OYST_API_SANDBOX_KEY_ONECLICK" name="OYST_API_SANDBOX_KEY_ONECLICK" value="{$oyst.OYST_API_SANDBOX_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                            </span>
                                        </div>
                                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                        {if $oyst.apikey_sandbox_test_error_oneclick}
                                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                        {/if}
                                    </div>
                                </div>
                                <div class="form-group clearfix env custom" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='1-Click API Custom Key' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <div class="input-group">
                                            <input type="text" id="OYST_API_CUSTOM_KEY_ONECLICK" name="OYST_API_CUSTOM_KEY_ONECLICK" value="{$oyst.OYST_API_CUSTOM_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                            </span>
                                        </div>
                                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                        {if $oyst.apikey_custom_test_error_oneclick}
                                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                        {/if}
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                  <label class="control-label col-md-3 col-lg-3">{l s='Environment' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <select name="OYST_API_ENV_ONECLICK">
                                            <option value="prod" {if $oyst.OYST_API_ENV_ONECLICK == 'prod'}selected="selected"{/if}>{l s='Production' mod='oyst'}</option>
                                            <option value="sandbox" {if $oyst.OYST_API_ENV_ONECLICK == 'sandbox'}selected="selected"{/if}>{l s='Sandbox' mod='oyst'}</option>
                                            <option value="custom" {if $oyst.OYST_API_ENV_ONECLICK == 'custom'}selected="selected"{/if}>{l s='Custom' mod='oyst'}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group clearfix env custom" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Endpoint API Custom' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <input type="text" id="OYST_API_CUSTOM_ENDPOINT_ONECLCK" name="OYST_API_CUSTOM_ENDPOINT_ONECLCK" value="{$oyst.OYST_API_CUSTOM_ENDPOINT_ONECLCK|escape:'htmlall':'UTF-8'}"/>
                                    </div>
                                </div>
                                <div class="form-group clearfix env custom" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Endpoint CDN Custom' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <input type="text" id="OYST_ONECLICK_URL_CUSTOM" name="OYST_ONECLICK_URL_CUSTOM" value="{$oyst.OYST_ONECLICK_URL_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-lg-3">{l s='State payment' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <select id="FC_OYST_STATE_PAYMENT_ONECLICK" name="FC_OYST_STATE_PAYMENT_ONECLICK">
                                            {foreach from=$oyst.order_state item=state}
                                                <option value="{$state.id_order_state|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_STATE_PAYMENT_ONECLICK == $state.id_order_state} selected="selected"{/if}>{$state.name|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" id="custom-btn-general" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Smart button' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_SMART_BTN" id="FC_OYST_SMART_BTN_ON" value="1" {if $oyst.FC_OYST_SMART_BTN == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_SMART_BTN_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_SMART_BTN" id="FC_OYST_SMART_BTN_OFF" value="0" {if $oyst.FC_OYST_SMART_BTN == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_SMART_BTN_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Border rounded' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BORDER_BTN" id="FC_OYST_BORDER_BTN_ON" value="1" {if $oyst.FC_OYST_BORDER_BTN == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BORDER_BTN_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BORDER_BTN" id="FC_OYST_BORDER_BTN_OFF" value="0" {if $oyst.FC_OYST_BORDER_BTN == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BORDER_BTN_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Style btn 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_THEME_BTN">
                                                <option value="default" {if $oyst.FC_OYST_THEME_BTN == 'default'}selected="selected"{/if}>{l s='Default' mod='oyst'}</option>
                                                <option value="inversed" {if $oyst.FC_OYST_THEME_BTN == 'inversed'}selected="selected"{/if}>{l s='Inversed' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Color' mod='oyst'}</label>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="col-md-3 col-lg-3">
                                                    <div class="input-group">
                                                        <input type="color" data-hex="true" class="color mColorPickerInput mColorPicker" name="FC_OYST_COLOR_BTN"  value="{if $oyst.FC_OYST_COLOR_BTN}{$oyst.FC_OYST_COLOR_BTN|escape:'htmlall':'UTF-8'}{else}#E91E63{/if}" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Custom CSS' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <textarea name="FC_OYST_CUSTOM_CSS" rows="10">{if $oyst.FC_OYST_CUSTOM_CSS}{$oyst.FC_OYST_CUSTOM_CSS|escape:'htmlall':'UTF-8'}{/if}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomGlobal" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_SMART_BTN" value="{$oyst.FC_OYST_SMART_BTN|intval}"/>
                                    <input type="hidden" name="FC_OYST_BORDER_BTN" value="{$oyst.FC_OYST_BORDER_BTN|intval}"/>
                                    <input type="hidden" name="FC_OYST_THEME_BTN" value="{$oyst.FC_OYST_THEME_BTN|intval}"/>
                                    <input type="hidden" name="FC_OYST_COLOR_BTN" value="{$oyst.FC_OYST_COLOR_BTN|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enabled button product' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_PRODUCT" id="FC_OYST_BTN_PRODUCT_ON" value="1" {if $oyst.FC_OYST_BTN_PRODUCT == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_PRODUCT_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_PRODUCT" id="FC_OYST_BTN_PRODUCT_OFF" value="0" {if $oyst.FC_OYST_BTN_PRODUCT == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_PRODUCT_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_PRODUCT" value="{$oyst.FC_OYST_WIDTH_BTN_PRODUCT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_PRODUCT" value="{$oyst.FC_OYST_HEIGHT_BTN_PRODUCT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_PRODUCT" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_PRODUCT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_PRODUCT" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_PRODUCT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_PRODUCT" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_PRODUCT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn add to cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_PRODUCT" value="{if $oyst.FC_OYST_ID_BTN_PRODUCT}{$oyst.FC_OYST_ID_BTN_PRODUCT|escape:'htmlall':'UTF-8'}{else}#add_to_cart{/if}"/>
                                            <span class="help-block">{l s='You can select multiple selectors separated by commas (jQuery selector)' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id smart btn' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_PRODUCT" value="{if $oyst.FC_OYST_ID_SMART_BTN_PRODUCT}{$oyst.FC_OYST_ID_SMART_BTN_PRODUCT|escape:'htmlall':'UTF-8'}{else}#add_to_cart button{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_PRODUCT">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_PRODUCT == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_PRODUCT == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomProduct" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_PRODUCT" value="{$oyst.FC_OYST_BTN_PRODUCT|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn-cart" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable button cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_CART" id="FC_OYST_BTN_CART_ON" value="1" {if $oyst.FC_OYST_BTN_CART == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_CART_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_CART" id="FC_OYST_BTN_CART_OFF" value="0" {if $oyst.FC_OYST_BTN_CART == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_CART_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_CART" value="{$oyst.FC_OYST_WIDTH_BTN_CART|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_CART" value="{$oyst.FC_OYST_HEIGHT_BTN_CART|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_CART" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_CART|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_CART" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_CART|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_CART" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_CART|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_CART" value="{if $oyst.FC_OYST_ID_BTN_CART}{$oyst.FC_OYST_ID_BTN_CART|escape:'htmlall':'UTF-8'}{else}.cart_navigation .button-medium{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id smart btn' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_CART" value="{if $oyst.FC_OYST_ID_SMART_BTN_CART}{$oyst.FC_OYST_ID_SMART_BTN_CART|escape:'htmlall':'UTF-8'}{else}.cart_navigation .button-medium{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_CART">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_CART == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_CART == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomCart" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_CART" value="{$oyst.FC_OYST_BTN_CART|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn-layer-cart" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable button layer cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_LAYER" id="FC_OYST_BTN_LAYER_ON" value="1" {if $oyst.FC_OYST_BTN_LAYER == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_LAYER_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_LAYER" id="FC_OYST_BTN_LAYER_OFF" value="0" {if $oyst.FC_OYST_BTN_LAYER == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_LAYER_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_LAYER" value="{$oyst.FC_OYST_WIDTH_BTN_LAYER|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_LAYER" value="{$oyst.FC_OYST_HEIGHT_BTN_LAYER|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_LAYER" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_LAYER|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_LAYER" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_LAYER|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_LAYER" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_LAYER|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_LAYER" value="{if $oyst.FC_OYST_ID_BTN_LAYER}{$oyst.FC_OYST_ID_BTN_LAYER|escape:'htmlall':'UTF-8'}{else}#layer_cart .button-container{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id smart btn' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_LAYER" value="{if $oyst.FC_OYST_ID_SMART_BTN_LAYER}{$oyst.FC_OYST_ID_SMART_BTN_LAYER|escape:'htmlall':'UTF-8'}{else}#layer_cart .button-container{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn layer 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_LAYER">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_LAYER == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_LAYER == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomLayer" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_LAYER" value="{$oyst.FC_OYST_BTN_LAYER|intval}"/>
                                    <input type="hidden" name="FC_OYST_WIDTH_BTN_LAYER" value="{$oyst.FC_OYST_WIDTH_BTN_LAYER|intval}"/>
                                    <input type="hidden" name="FC_OYST_HEIGHT_BTN_LAYER" value="{$oyst.FC_OYST_HEIGHT_BTN_LAYER|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn-login" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable button login' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_LOGIN" id="FC_OYST_BTN_LOGIN_ON" value="1" {if $oyst.FC_OYST_BTN_LOGIN == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_LOGIN_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_LOGIN" id="FC_OYST_BTN_LOGIN_OFF" value="0" {if $oyst.FC_OYST_BTN_LOGIN == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_LOGIN_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_LOGIN" value="{$oyst.FC_OYST_WIDTH_BTN_LOGIN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_LOGIN" value="{$oyst.FC_OYST_HEIGHT_BTN_LOGIN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_LOGIN" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_LOGIN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_LOGIN" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_LOGIN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_LOGIN" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_LOGIN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_LOGIN" value="{if $oyst.FC_OYST_ID_BTN_LOGIN}{$oyst.FC_OYST_ID_BTN_LOGIN|escape:'htmlall':'UTF-8'}{else}#SubmitCreate{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart for form' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_LOGIN" value="{if $oyst.FC_OYST_ID_SMART_BTN_LOGIN}{$oyst.FC_OYST_ID_SMART_BTN_LOGIN|escape:'htmlall':'UTF-8'}{else}#SubmitCreate{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn login 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_LOGIN">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_LOGIN == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_LOGIN == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomLogin" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_LOGIN" value="{$oyst.FC_OYST_BTN_LOGIN|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn-payment" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable button payment' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_PAYMENT" id="FC_OYST_BTN_PAYMENT_ON" value="1" {if $oyst.FC_OYST_BTN_PAYMENT == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_PAYMENT_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_PAYMENT" id="FC_OYST_BTN_PAYMENT_OFF" value="0" {if $oyst.FC_OYST_BTN_PAYMENT == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_PAYMENT_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_PAYMENT" value="{$oyst.FC_OYST_WIDTH_BTN_PAYMENT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_PAYMENT" value="{$oyst.FC_OYST_HEIGHT_BTN_PAYMENT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_PAYMENT" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_PAYMENT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_PAYMENT" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_PAYMENT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_PAYMENT" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_PAYMENT|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_PAYMENT" value="{if $oyst.FC_OYST_ID_BTN_PAYMENT}{$oyst.FC_OYST_ID_BTN_PAYMENT|escape:'htmlall':'UTF-8'}{else}#HOOK_PAYMENT{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn cart for form' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_PAYMENT" value="{if $oyst.FC_OYST_ID_SMART_BTN_PAYMENT}{$oyst.FC_OYST_ID_SMART_BTN_PAYMENT|escape:'htmlall':'UTF-8'}{else}.payment_module{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn login 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_PAYMENT">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_PAYMENT == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_PAYMENT == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomPayment" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_PAYMENT" value="{$oyst.FC_OYST_BTN_PAYMENT|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="custom-btn-address" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable button address' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_BTN_ADDR" id="FC_OYST_BTN_ADDR_ON" value="1" {if $oyst.FC_OYST_BTN_ADDR == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_ADDR_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_BTN_ADDR" id="FC_OYST_BTN_ADDR_OFF" value="0" {if $oyst.FC_OYST_BTN_ADDR == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_BTN_ADDR_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN_ADDR" value="{$oyst.FC_OYST_WIDTH_BTN_ADDR|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN_ADDR" value="{$oyst.FC_OYST_HEIGHT_BTN_ADDR|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_TOP_BTN_ADDR" value="{$oyst.FC_OYST_MARGIN_TOP_BTN_ADDR|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_LEFT_BTN_ADDR" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN_ADDR|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN_ADDR" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN_ADDR|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn address' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_BTN_ADDR" value="{if $oyst.FC_OYST_ID_BTN_ADDR}{$oyst.FC_OYST_ID_BTN_ADDR|escape:'htmlall':'UTF-8'}{else}#submitAddress{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Id btn address for form' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_ID_SMART_BTN_ADDR" value="{if $oyst.FC_OYST_ID_SMART_BTN_ADDR}{$oyst.FC_OYST_ID_SMART_BTN_ADDR|escape:'htmlall':'UTF-8'}{else}#submitAddress{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn login 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN_ADDR">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN_ADDR == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN_ADDR == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustomAddress" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_BTN_ADDR" value="{$oyst.FC_OYST_BTN_ADDR|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="settings-carrier" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Carrier default' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_SHIPMENT_DEFAULT">
                                                <option value="0">{l s='Choose carrier default' mod='oyst'}</option>
                                                {foreach $oyst.carrier_list as $carrier}
                                                    <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}"{if $oyst.shipment_default == $carrier.id_reference} selected="selected"{/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            </select>
                                            <p class="error-carrier_default help-block" style="color:red;font-weight:bold;">{l s='Please select one carrier default' mod='oyst'}</p>
                                        </div>
                                    </div>
                                    {foreach $oyst.carrier_list as $carrier}
                                        <div class="form-group clearfix">
                                            <label class="control-label col-md-3 col-lg-3">{$carrier.name|escape:'htmlall':'UTF-8'}</label>
                                            <div class="col-md-4 col-lg-4">
                                                <select name="FC_OYST_SHIPMENT_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
                                                    <option value="0">{l s='Disabled' mod='oyst'}</option>
                                                    {foreach from=$oyst.type_list key=value item=name}
                                                        <option value="{$value|escape:'htmlall':'UTF-8'}" {if $value ==  Configuration::get("FC_OYST_SHIPMENT_{$carrier.id_reference}")}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                </select>
                                                <p class="error-carrier_{$carrier.id_reference}_type error-carrier_type help-block" style="color:red;font-weight:bold;">{l s='Please select type home delivery for carrier default' mod='oyst'}</p>
                                            </div>
                                            <label class="control-label col-md-2 col-lg-2">{l s='Delay in days' mod='oyst'}</label>
                                            <div class="col-md-1 col-lg-1">
                                              <input type="text" name="FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{else}3{/if}" />
                                            </div>
                                        </div>
                                    {/foreach}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Bussiness days' mod='oyst'}</label>
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th class="fixed-width-xs">
                                                                    <span class="title_box"><input type="checkbox" name="checkme" id="checkme" onclick="checkDelBoxes(this.form, 'oyst_days[]', this.checked)"></span>
                                                                </th>
                                                                <th class="fixed-width-xs">
                                                                    <span class="title_box">{l s='Day of week' mod='oyst'}</span>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {foreach $oyst.days key=identifiant item=day}
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="oyst_days[]" class="oyst_days" id="oyst_days_{$identifiant|intval}" value="{$identifiant|intval}" {if in_array($identifiant, $oyst.restriction_business_days)}checked="checked"{/if}>
                                                                    </td>
                                                                    <td>{$day|escape:'htmlall':'UTF-8'}</td>
                                                                </tr>
                                                            {/foreach}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {else}
                                    {foreach $oyst.restriction_business_days item=day}
                                        <input type="hidden" name="oyst_days[]" value="{$day|intval}"/>
                                    {/foreach}
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="settings-advanced" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix urlCustomization">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Confirmation url for button cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select id="FC_OYST_OC_REDIRECT_CONF" name="FC_OYST_OC_REDIRECT_CONF">
                                            {foreach from=$oyst.redirect_oc_conf_urls key=url item=label}
                                                <option value="{$url|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_OC_REDIRECT_CONF == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                            </select>
                                            <input type="text" id="FC_OYST_OC_REDIRECT_CONF_CUSTOM" name="FC_OYST_OC_REDIRECT_CONF_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_OC_REDIRECT_CONF_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                                            {if $oyst.custom_conf_error}
                                                <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Delay' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" id="FC_OYST_DELAY" name="FC_OYST_DELAY" value="{if $oyst.FC_OYST_DELAY}{$oyst.FC_OYST_DELAY|escape:'htmlall':'UTF-8'}{else}15{/if}"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Manage quantity' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_MANAGE_QUANTITY" id="FC_OYST_MANAGE_QUANTITY_ON" value="1" {if $oyst.FC_OYST_MANAGE_QUANTITY == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_MANAGE_QUANTITY_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_MANAGE_QUANTITY" id="FC_OYST_MANAGE_QUANTITY_OFF" value="0" {if $oyst.FC_OYST_MANAGE_QUANTITY == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_MANAGE_QUANTITY_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable advanced stock' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_SHOULD_AS_STOCK" id="FC_OYST_SHOULD_AS_STOCK_ON" value="1" {if $oyst.FC_OYST_SHOULD_AS_STOCK == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_SHOULD_AS_STOCK_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_SHOULD_AS_STOCK" id="FC_OYST_SHOULD_AS_STOCK_OFF" value="0" {if $oyst.FC_OYST_SHOULD_AS_STOCK == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_SHOULD_AS_STOCK_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Manage quantity for cart' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7" style="height: 31px;">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="FC_OYST_MANAGE_QUANTITY_CART" id="FC_OYST_MANAGE_QUANTITY_CART_ON" value="1" {if $oyst.FC_OYST_MANAGE_QUANTITY_CART == 1} checked="checked"{/if}>
                                                <label for="FC_OYST_MANAGE_QUANTITY_CART_ON" class="radioCheck">
                                                    {l s='Yes' mod='oyst'}
                                                </label>
                                                <input type="radio" name="FC_OYST_MANAGE_QUANTITY_CART" id="FC_OYST_MANAGE_QUANTITY_CART_OFF" value="0" {if $oyst.FC_OYST_MANAGE_QUANTITY_CART == 0} checked="checked"{/if}>
                                                <label for="FC_OYST_MANAGE_QUANTITY_CART_OFF" class="radioCheck">
                                                    {l s='No' mod='oyst'}
                                                </label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Enable only for ip' mod='oyst'}</label>
                                        <div class="col-md-6 col-lg-6">
                                            <input type="text" name="FC_OYST_ONLY_FOR_IP" id="FC_OYST_ONLY_FOR_IP" value="{$oyst.FC_OYST_ONLY_FOR_IP|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='IP address split by a comma' mod='oyst'}</span>
                                        </div>
                                        <div class="col-md-1 col-md-1">
                                            <button type="button" class="btn btn-default" id="get-remote-addr"><i class="icon-plus"></i>{l s='Add my IP' mod='oyst'}</button>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-3 col-lg-offset-3 col-md-7 col-lg-7">
                                            <button type="submit" value="1" name="submitOystConfigurationReset" class="btn btn-success">
                                                 <strong>{l s='Reset product' mod='oyst'}</strong>
                                            </button>
                                            <button type="submit" value="1" name="submitOystConfigurationDisable" class="btn btn-danger">
                                                 <strong>{l s='Disable product' mod='oyst'}</strong>
                                            </button>
                                        </div>
                                    </div>
                                {else}
                                    <input type="hidden" name="FC_OYST_OC_REDIRECT_CONF" value="{$oyst.FC_OYST_OC_REDIRECT_CONF|intval}"/>
                                    <input type="hidden" name="FC_OYST_MANAGE_QUANTITY" value="{$oyst.FC_OYST_MANAGE_QUANTITY|intval}"/>
                                    <input type="hidden" name="FC_OYST_SHOULD_AS_STOCK" value="{$oyst.FC_OYST_SHOULD_AS_STOCK|intval}"/>
                                    <input type="hidden" name="FC_OYST_MANAGE_QUANTITY_CART" value="{$oyst.FC_OYST_MANAGE_QUANTITY_CART|intval}"/>
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="settings-restrictions" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="row table-responsive clearfix ">
                                        <div class="col-xs-6 overflow-y">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40%"><span class="title_box">{l s='Restrictions of languages' mod='oyst'}</span></th>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {foreach $oyst.languages as $lang}
                                                        <tr>
                                                            <td><span>{$lang['name']|escape:'htmlall':'UTF-8'}</span></td>
                                                            <td class="text-center"><input name="oyst_lang[]" value="{$lang['id_lang']|intval}" {if in_array($lang['id_lang'], $oyst.restriction_languages)}checked="checked"{/if} type="checkbox"></td>
                                                        </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                {else}
                                    {foreach $oyst.languages as $lang}
                                        <input style="display:none;" name="oyst_lang[]" value="{$lang['id_lang']|intval}" {if in_array($lang['id_lang'], $oyst.restriction_languages)}checked="checked"{/if} type="checkbox">
                                    {/foreach}
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="display-notifications" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">Tables</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="table_selector" id="table-selector">
                                                {foreach from=$oyst.notification_tables item=notification_table}
                                                    <option value="{$notification_table|escape:'htmlall':'UTF-8'}">{$notification_table|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                    <table id="notification-table" class="display nowrap" cellspacing="0" width="100%"></table>
                                {else}
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <p>
                <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-info">
                    <strong>{l s='Save' mod='oyst'}</strong>
                </button>
            </p>
        </div>
    </div>
</form>
{/if}

<fieldset id="logManagement">
    <legend>Logs</legend>
    <p>Display logs about any process</p>

    {if is_array($oyst.logsFile) && count($oyst.logsFile)}
        <select name="logsFile">
            <option value="">{l s='Select log' mod='oyst'}</option>
            {foreach $oyst.logsFile as $logFile}
                <option value="{$logFile|escape:'htmlall':'UTF-8'}">{$logFile|escape:'htmlall':'UTF-8'|substr:0:-4}</a></option>
            {/foreach}
        </select>

        <form method="post" id="deleteLogs">
            <input type="submit" name="deleteLogs" value="{l s='Delete logs' mod='oyst'}" />
        </form>
    {else}
        {l s='Actually there are no logs available' mod='oyst'}
    {/if}
</fieldset>

<div id="logContainer">
    <p id="logName"></p>
    <pre id="log"></pre>
</div>

<script>
    var currentTab = "{$oyst.current_tab|escape:'html':'UTF-8'}";
</script>
<script>
    $(function() {
        var logManagement = new LogManagement(window.location.href);
        logManagement.initBackend();
    });
</script>
