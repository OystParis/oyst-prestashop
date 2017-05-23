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
<link type="text/css" rel="stylesheet" href="{$oyst.module_dir|escape:'html':'UTF-8'}views/css/freepay-1.5.css" media="all">

{if isset($oyst.result) && $oyst.result eq 'ok'}
    <p class="conf"><strong>{l s='The new configuration has been saved!' mod='oyst'}</strong></p>
{/if}

{if !$oyst.allow_url_fopen_check}
    <p class="error"><strong>{l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}</strong></p>
{/if}
{if !$oyst.curl_check}
    <p class="error"><strong>{l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}</strong></p>
{/if}

{if isset($apiError)}
    <p class="error"><strong>{l s='Got an API error:' mod='oyst'}</strong> {$apiError}</p>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
    <form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="#">
        <div class="header">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
            <p class="sub-header"><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
        </div>
        {if $oyst.FC_OYST_GUEST && $oyst.phone}
        <div class="header">
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
        <fieldset class="panel">
            <legend>
                <img src="{$oyst.module_dir|escape:'html':'UTF-8'}logo.png" alt="" width="16">{l s='Configuration' mod='oyst'}
            </legend>

            <label>{l s='Environment' mod='oyst'}</label>
            <div class="margin-form">
                <select name="FC_OYST_API_ENV">
                    <option value="prod" {if $oyst.FC_OYST_API_ENV == 'prod'}selected="selected"{/if}>Production</option>
                    <option value="preprod" {if $oyst.FC_OYST_API_ENV == 'preprod'}selected="selected"{/if}>Preproduction</option>
                </select>
                {if $oyst.apikey_test_error}
                    <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                {/if}
            </div>

            <label class="env prod">{l s='Endpoint API Production' mod='oyst'}</label>
            <div class="margin-form env prod">
                <input type="text" id="OYST_API_PROD_ENDPOINT" name="OYST_API_PROD_ENDPOINT" value="{$oyst.OYST_API_PROD_ENDPOINT|escape:'htmlall':'UTF-8'}"/>
            </div>

            <label class="env preprod">{l s='Endpoint API PreProduction' mod='oyst'}</label>
            <div class="margin-form env preprod">
                <input type="text" id="OYST_API_PREPROD_ENDPOINT" name="OYST_API_PREPROD_ENDPOINT" value="{$oyst.OYST_API_PREPROD_ENDPOINT|escape:'htmlall':'UTF-8'}"/>
            </div>

            <div class="env prod" style="display: none;">
                <label>{l s='API Production Key' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="FC_OYST_API_PROD_KEY" name="FC_OYST_API_PROD_KEY" value="{$oyst.FC_OYST_API_PROD_KEY|escape:'htmlall':'UTF-8'}"/>
                    <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                    {if $oyst.apikey_test_error}
                    <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                    {/if}
                </div>
            </div>

            <div class="env preprod" style="display: none;">
                <label>{l s='API PreProduction Key' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="FC_OYST_API_PREPROD_KEY" name="FC_OYST_API_PREPROD_KEY" value="{$oyst.FC_OYST_API_PREPROD_KEY|escape:'htmlall':'UTF-8'}"/>
                    <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                    {if $oyst.apikey_test_error}
                    <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                    {/if}
                </div>
            </div>

            <label>{l s='Enable FreePay' mod='oyst'}</label>
            <div class="margin-form">
                <input type="checkbox" class="form-control" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
            </div>


            <label>{l s='Enable OneClick' mod='oyst'}</label>
            <div class="margin-form">
                <input type="checkbox" class="form-control" name="OYST_ONE_CLICK_FEATURE_STATE" value="1"{if $oyst.OYST_ONE_CLICK_FEATURE_STATE} checked="checked"{/if} />
            </div>
            <label class="advancedOptions">{l s='Success Url' mod='oyst'}</label>
            <div class="margin-form advancedOptions urlCustomization">
                <select id="FC_OYST_REDIRECT_SUCCESS" name="FC_OYST_REDIRECT_SUCCESS">
                {foreach from=$oyst.redirect_success_urls key=url item=label}
                    <option value="{$url|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_REDIRECT_SUCCESS == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label|escape:'html':'UTF-8'}</option>
                {/foreach}
                </select>
                <input type="text" id="FC_OYST_REDIRECT_SUCCESS_CUSTOM" name="FC_OYST_REDIRECT_SUCCESS_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_SUCCESS_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                {if $oyst.custom_success_error}
                <p class="error customUrlText"><strong>{l s='This is not a valid URL!' mod='oyst'}</strong></p>
                {/if}
            </div>
            <label class="advancedOptions">{l s='Error Url' mod='oyst'}</label>
            <div class="margin-form advancedOptions urlCustomization">
                <select id="FC_OYST_REDIRECT_ERROR" name="FC_OYST_REDIRECT_ERROR">
                {foreach from=$oyst.redirect_error_urls key=url item=label}
                    <option value="{$url|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_REDIRECT_ERROR == $url} selected="selected"{/if}{if $url == 'CUSTOM'} class="customUrl"{/if}>{$label|escape:'html':'UTF-8'}</option>
                {/foreach}
                </select>
                <input type="text" id="FC_OYST_REDIRECT_ERROR_CUSTOM" name="FC_OYST_REDIRECT_ERROR_CUSTOM" class="customUrlText" disabled="disabled" value="{$oyst.FC_OYST_REDIRECT_ERROR_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                {if $oyst.custom_error_error}
                <p class="error customUrlText"><strong>{l s='This is not a valid URL!' mod='oyst'}</strong></p>
                {/if}
            </div>
            <br>
            <div class="margin-form">
                <p>
                    <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration">
                        {l s='Save' mod='oyst'}
                    </button>
                    <button id="toggleConfig" type="button">
                        <span>{l s='Show advanced options' mod='oyst'}</span>
                        <span style="display: none;">{l s='Hide advanced options' mod='oyst'}</span>
                    </button>
                </p>
            </div>
        </fieldset>
    </form>
    {if $oyst.isCurrentApiKeyValid}
    <br />
    <form method="POST">
        <fieldset>
            <legend>
                <img src="{$oyst.module_dir|escape:'html':'UTF-8'}logo.png" alt="" width="16">{l s='Catalog' mod='oyst'}
            </legend>

            <label>{l s='Syncronize your products' mod='oyst'}</label>
            <div class="margin-form">
                {if $oyst.exportRunning}
                    {l s='An export is currently running, please wait until it\'s over' mod='oyst'}
                {else}
                    <button type="submit" name="synchronizeProducts">
                        {if $oyst.lastExportDate}
                            {l s='Re start the export process' mod='oyst'}
                        {else}
                            {l s='Start the export process' mod='oyst'}
                        {/if}
                    </button>
                    <p>{l s='Will export your products to Oyst'}</p>
                {/if}
            </div>
        </fieldset>
    </form>
    {/if}
{/if}

<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/handleAdvancedConf.js"></script>
