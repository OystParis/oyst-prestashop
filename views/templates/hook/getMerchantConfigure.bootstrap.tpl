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

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
<form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="">
    <div align="center" style="font-size: 16px;">
        <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
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
    <div class="panel" class="oyst_fieldset">
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
                        <div class="form-group clearfix env preprod" style="display: none;">
                            <label class="control-label col-md-3 col-lg-3">{l s='FreePay API PreProduction Key' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7">
                                <div class="input-group">
                                    <input type="text" id="OYST_API_PREPROD_KEY_FREEPAY" name="OYST_API_PREPROD_KEY_FREEPAY" value="{$oyst.OYST_API_PREPROD_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                    </span>
                                </div>
                                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.staging.oyst.eu/signup" target="_blank">backoffice.staging.oyst.com</a></p>
                                <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                                {if $oyst.apikey_preprod_test_error_freepay}
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
                                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.sandbox.oyst.eu/signup" target="_blank">backoffice.sandbox.oyst.com</a></p>
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
                                    <option value="preprod" {if $oyst.OYST_API_ENV_FREEPAY == 'preprod'}selected="selected"{/if}>{l s='Preproduction' mod='oyst'}</option>
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
                        <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Create order before payment' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7" style="height: 31px;">
                                <input type="checkbox" id="FC_OYST_PREORDER_FEATURE" name="FC_OYST_PREORDER_FEATURE" value="1"{if $oyst.FC_OYST_PREORDER_FEATURE} checked="checked"{/if} />
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-lg-3">{l s='Enable Fraudscoring' mod='oyst'}</label>
                            <div class="col-md-7 col-lg-7" style="height: 31px;">
                                <input type="checkbox" name="FC_OYST_ACTIVE_FRAUD" value="1"{if $oyst.FC_OYST_ACTIVE_FRAUD} checked="checked"{/if} />
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div role="tabpanel" class="panel tab-pane{if $oyst.current_tab == '#tab-content-1-click'} active{/if}" id="tab-content-1-click" style="border-top: 0;border-radius: initial;">
                    <div class="row">
                        <div class="col-lg-2 col-md-3">
                            <ul class="nav nav-pills nav-stacked" role="tablist">
                                <li role="presentation" class="active"><a role="tab" data-toggle="tab" href="#conf-oc"/>{l s='Configuration One-click' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#custom-btn"/>{l s='Custom of button' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#settings-carrier" />{l s='Settings carrier' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#settings-advanced" />{l s='Settings advanced' mod='oyst'}</a></li>
                                <li role="presentation" class="" ><a role="tab" data-toggle="tab" href="#settings-restrictions" />{l s='Settings restrictions' mod='oyst'}</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-10 col-md-9 tab-content">
                            <div id="conf-oc" class="tab-pane active" role="tabpanel">
                                <div class="form-group clearfix">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Enable OneClick' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7" style="height: 31px;">
                                        <input type="checkbox" name="OYST_ONE_CLICK_FEATURE_STATE" value="1"{if $oyst.OYST_ONE_CLICK_FEATURE_STATE} checked="checked"{/if} />
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
                                <div class="form-group clearfix env preprod" style="display: none;">
                                    <label class="control-label col-md-3 col-lg-3">{l s='1-Click API PreProduction Key' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <div class="input-group">
                                            <input type="text" id="OYST_API_PREPROD_KEY_ONECLICK" name="OYST_API_PREPROD_KEY_ONECLICK" value="{$oyst.OYST_API_PREPROD_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-info module_form_apply_btn" type="submit" name="submitOystConfiguration">{l s='Apply' mod='oyst'}</button>
                                            </span>
                                        </div>
                                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                        {if $oyst.apikey_preprod_test_error_oneclick}
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
                                            <option value="preprod" {if $oyst.OYST_API_ENV_ONECLICK == 'preprod'}selected="selected"{/if}>{l s='Preproduction' mod='oyst'}</option>
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
                            <div role="tabpanel" id="custom-btn" class="tab-pane">
                                {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Style btn 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_THEME_BTN">
                                                <option value="normal" {if $oyst.FC_OYST_THEME_BTN == 'normal'}selected="selected"{/if}>{l s='Normal' mod='oyst'}</option>
                                                <option value="inversed" {if $oyst.FC_OYST_THEME_BTN == 'inversed'}selected="selected"{/if}>{l s='Inversed' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Color' mod='oyst'}</label>
                                        <div class="col-lg-7">
                                            <div class="form-group">
                                                <div class="col-md-2 col-lg-2">
                                                    <div class="input-group">
                                                        <input type="color" data-hex="true" class="color mColorPickerInput mColorPicker" name="FC_OYST_COLOR_BTN"  value="{if $oyst.FC_OYST_COLOR_BTN}{$oyst.FC_OYST_COLOR_BTN|escape:'htmlall':'UTF-8'}{else}#E91E63{/if}" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_WIDTH_BTN" value="{$oyst.FC_OYST_WIDTH_BTN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <input type="text" name="FC_OYST_HEIGHT_BTN" value="{$oyst.FC_OYST_HEIGHT_BTN|escape:'htmlall':'UTF-8'}"/>
                                            <span class="help-block">{l s='In % or px' mod='oyst'}</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="control-label col-md-3 col-lg-3">{l s='Position btn 1-Click' mod='oyst'}</label>
                                        <div class="col-md-7 col-lg-7">
                                            <select name="FC_OYST_POSITION_BTN">
                                                <option value="before" {if $oyst.FC_OYST_POSITION_BTN == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                                <option value="after" {if $oyst.FC_OYST_POSITION_BTN == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-md-offset-9 col-lg-offset-9 col-md-1 col-lg-1">
                                            <button type="submit" value="1" name="submitOystResetCustom" class="btn btn-info module_form_reset_btn">
                                               <strong>{l s='Reset' mod='oyst'}</strong>
                                           </button>
                                        </div>
                                    </div>
                                {else}
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
                                            </div>
                                            <label class="control-label col-md-2 col-lg-2">{l s='Delay in days' mod='oyst'}</label>
                                            <div class="col-md-1 col-lg-1">
                                              <input type="text" name="FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{else}3{/if}" />
                                            </div>
                                        </div>
                                    {/foreach}
                                {else}
                                    <div class="alert alert-warning" role="alert">
                                        <p>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</p>
                                    </div>
                                {/if}
                            </div>
                            <div role="tabpanel" id="settings-advanced" class="tab-pane">
                                <div class="form-group clearfix">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Delay' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7">
                                        <input type="text" id="FC_OYST_DELAY" name="FC_OYST_DELAY" value="{if $oyst.FC_OYST_DELAY}{$oyst.FC_OYST_DELAY|escape:'htmlall':'UTF-8'}{else}15{/if}"/>
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Manage quantity' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7" style="height: 31px;">
                                        <input type="checkbox" name="FC_OYST_MANAGE_QUANTITY" value="1"{if $oyst.FC_OYST_MANAGE_QUANTITY} checked="checked"{/if} />
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="control-label col-md-3 col-lg-3">{l s='Enable advanced stock' mod='oyst'}</label>
                                    <div class="col-md-7 col-lg-7" style="height: 31px;">
                                        <input type="checkbox" name="FC_OYST_SHOULD_AS_STOCK" value="1"{if $oyst.FC_OYST_SHOULD_AS_STOCK} checked="checked"{/if} />
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
                            </div>
                            <div role="tabpanel" id="settings-restrictions" class="tab-pane">
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
                                                        <td><span>{$lang['name']}</span></td>
                                                        <td class="text-center"><input name="oyst_lang[]" value="{$lang['id_lang']}" {if in_array($lang['id_lang'], $oyst.restriction_languages)}checked="checked"{/if} type="checkbox"></td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
