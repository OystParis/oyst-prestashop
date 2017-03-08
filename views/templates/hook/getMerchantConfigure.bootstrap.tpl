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

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
<form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="">
    <div align="center" style="font-size: 16px;">
        <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
        <p><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
    </div>
    {if $oyst.FC_OYST_GUEST && $oyst.phone}
    <div class="text-center">
        <p>{$oyst.message|escape:'html':'UTF-8'} <strong>{$oyst.phone|escape:'html':'UTF-8'}</strong></p>

        <p><a href="{$oyst.configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8'}">{l s='Change your phone number' mod='oyst'}</a></p>
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
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Configuration' mod='oyst'}
        </div>
        <div class="oyst-admin-tab">
            <fieldset>
                <div class="form-group clearfix">
                    <label class="control-label col-md-3 col-lg-4">{l s='API Key' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4">
                        <input type="text" id="FC_OYST_API_KEY" name="FC_OYST_API_KEY" value="{$oyst.FC_OYST_API_KEY|escape:'htmlall':'UTF-8'}" />
                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                        {if $oyst.apikey_test_error}
                        <div class="alert alert-danger">{l s='Your key seems invalid!' mod='oyst'}</div>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-md-3 col-lg-4">{l s='Enable FreePay' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4" style="height: 31px;">
                        <input type="checkbox" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
                    </div>
                </div>
                <div class="form-group clearfix advancedOptions">
                    <label class="control-label col-md-3 col-lg-4">{l s='Set the Oyst payment endpoint' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4">
                        <input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                    </div>
                </div>
                <div class="form-group clearfix advancedOptions urlCustomization">
                    <label class="control-label col-md-3 col-lg-4">{l s='Success Url' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4">
                        <select id="FC_OYST_REDIRECT_SUCCESS" name="FC_OYST_REDIRECT_SUCCESS">
                        {foreach from=$oyst.redirect_success_urls key=url item=label}
                            <option value="{$url}"{if $oyst.FC_OYST_REDIRECT_SUCCESS == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label}</option>
                        {/foreach}
                        </select>
                        <input type="text" id="FC_OYST_REDIRECT_SUCCESS_CUSTOM" name="FC_OYST_REDIRECT_SUCCESS_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_SUCCESS_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                        {if $oyst.custom_success_error}
                        <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix advancedOptions urlCustomization">
                    <label class="control-label col-md-3 col-lg-4">{l s='Error Url' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4">
                        <select id="FC_OYST_REDIRECT_ERROR" name="FC_OYST_REDIRECT_ERROR">
                        {foreach from=$oyst.redirect_error_urls key=url item=label}
                            <option value="{$url}"{if $oyst.FC_OYST_REDIRECT_ERROR == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label}</option>
                        {/foreach}
                        </select>
                        <input type="text" id="FC_OYST_REDIRECT_ERROR_CUSTOM" name="FC_OYST_REDIRECT_ERROR_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_ERROR_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                        {if $oyst.custom_error_error}
                        <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix advancedOptions urlCustomization">
                    <label class="control-label col-md-3 col-lg-4">{l s='Cancel Url' mod='oyst'}</label>
                    <div class="col-md-7 col-lg-4">
                        <select id="FC_OYST_REDIRECT_CANCEL" name="FC_OYST_REDIRECT_CANCEL">
                        {foreach from=$oyst.redirect_cancel_urls key=url item=label}
                            <option value="{$url}"{if $oyst.FC_OYST_REDIRECT_CANCEL == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label}</option>
                        {/foreach}
                        </select>
                        <input type="text" id="FC_OYST_REDIRECT_CANCEL_CUSTOM" name="FC_OYST_REDIRECT_CANCEL_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_CANCEL_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                        {if $oyst.custom_cancel_error}
                        <div class="alert alert-danger customUrlText">{l s='This is not a valid URL!' mod='oyst'}</div>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4"></label>
                    <div class="col-lg-4">
                        <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-info form-control bigger-">
                            <strong>{l s='Save' mod='oyst'}</strong>
                        </button>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="panel-footer">
            <button id="toggleConfig" type="button" class="btn btn-default">
                <i class="process-icon- icon-eye"></i> <span>{l s='Show advanced options' mod='oyst'}</span><span style="display: none;">{l s='Hide advanced options' mod='oyst'}</span>
            </button>
            {if !$oyst.FC_OYST_API_KEY}
            <a class="btn btn-default" href="{$oyst.configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8' }">
                <i class="process-icon- icon-key"></i>
                {l s='Get an API Key' mod='oyst'}
            </a>
            {/if}
        </div>
    </div>
</form>
{/if}

<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/handleAdvancedConf.js"></script>
