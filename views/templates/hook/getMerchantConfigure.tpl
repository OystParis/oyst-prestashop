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
    <p class="error"><strong>{l s='Got an API error:' mod='oyst'}</strong> {$apiError|escape:'htmlall':'UTF-8'}</p>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
    <div class="oyst configuration">
        <div class="header">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
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
        <div class="productTabs">
            <ul id="oyst-config-menu" class="tab">
                <li class="tab-row">
                    <a class="tab-page selected" href="#tab-content-FreePay">FreePay</a>
                </li>
                <li class="tab-row">
                    <a class="tab-page" href="#tab-content-1-click">1-Click</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="tabPane1">
            <form id="module_form" class="defaultForm form-horizontal" method="POST" action="#">
                <input type="hidden" id="current_tab_value" name="current_tab" value="{$oyst.current_tab}"/>
                <div id="tab-content-FreePay" class="tab-pane">
                    <label>{l s='Enable FreePay' mod='oyst'}</label>
                    <div class="margin-form">
                        <input type="checkbox" class="form-control" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
                    </div>
                    <div class="env prod" style="display: none;">
                        <label>{l s='FreePay API Production Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_PROD_KEY_FREEPAY" name="OYST_API_PROD_KEY_FREEPAY" value="{$oyst.OYST_API_PROD_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                            {if $oyst.apikey_prod_test_error_freepay}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <div class="env preprod" style="display: none;">
                        <label>{l s='FreePay API PreProduction Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_PREPROD_KEY_FREEPAY" name="OYST_API_PREPROD_KEY_FREEPAY" value="{$oyst.OYST_API_PREPROD_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                            {if $oyst.apikey_preprod_test_error_freepay}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <div class="env custom" style="display: none;">
                        <label>{l s='FreePay API Custom Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_CUSTOM_KEY_FREEPAY" name="OYST_API_CUSTOM_KEY_FREEPAY" value="{$oyst.OYST_API_CUSTOM_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                            {if $oyst.apikey_custom_test_error_freepay}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <label>{l s='Success Url' mod='oyst'}</label>
                    <div class="margin-form urlCustomization">
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
                    <label>{l s='Error Url' mod='oyst'}</label>
                    <div class="margin-form urlCustomization">
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
                    <label>{l s='Environment' mod='oyst'}</label>
                    <div class="margin-form">
                        <select name="OYST_API_ENV_FREEPAY">
                            <option value="prod" {if $oyst.OYST_API_ENV_FREEPAY == 'prod'}selected="selected"{/if}>{l s='Production' mod='oyst'}</option>
                            <option value="preprod" {if $oyst.OYST_API_ENV_FREEPAY == 'preprod'}selected="selected"{/if}>{l s='Preproduction' mod='oyst'}</option>
                            <option value="custom" {if $oyst.OYST_API_ENV_FREEPAY == 'custom'}selected="selected"{/if}>{l s='Custom' mod='oyst'}</option>
                        </select>
                    </div>
                    <label class="env custom">{l s='Endpoint API Custom' mod='oyst'}</label>
                    <div class="margin-form env custom">
                        <input type="text" id="OYST_API_CUSTOM_ENDPOINT_FREEPAY" name="OYST_API_CUSTOM_ENDPOINT_FREEPAY" value="{$oyst.OYST_API_CUSTOM_ENDPOINT_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
                <div id="tab-content-1-click" class="tab-pane">
                    <label>{l s='Enable OneClick' mod='oyst'}</label>
                    <div class="margin-form">
                        <input type="checkbox" class="form-control" name="OYST_ONE_CLICK_FEATURE_STATE" value="1"{if $oyst.OYST_ONE_CLICK_FEATURE_STATE} checked="checked"{/if} />
                    </div>
                    <div class="env prod" style="display: none;">
                        <label>{l s='1-Click API Production Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_PROD_KEY_ONECLICK" name="OYST_API_PROD_KEY_ONECLICK" value="{$oyst.OYST_API_PROD_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            {if $oyst.apikey_prod_test_error_oneclick}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <div class="env preprod" style="display: none;">
                        <label>{l s='1-Click API PreProduction Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_PREPROD_KEY_ONECLICK" name="OYST_API_PREPROD_KEY_ONECLICK" value="{$oyst.OYST_API_PREPROD_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            {if $oyst.apikey_preprod_test_error_oneclick}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <div class="env custom" style="display: none;">
                        <label>{l s='1-Click API Custom Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_CUSTOM_KEY_ONECLICK" name="OYST_API_CUSTOM_KEY_ONECLICK" value="{$oyst.OYST_API_CUSTOM_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                            {if $oyst.apikey_custom_test_error_oneclick}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <label>{l s='Shipments' mod='oyst'}</label>
                    <div class="margin-form">
                        <div id="shipment-collection">
                            {foreach from=$oyst.shipment_list key=index item=shipment}
                            <div class="shipment-item">
                                <label>{l s='Carrier' mod='oyst'}</label>
                                <div class="margin-form">
                                    <select name="shipments[{$index}][id_carrier]">
                                    {foreach $oyst.carrier_list as $carrier}
                                        <option value="{$carrier.id_reference}"{if $shipment.id_carrier_reference == $carrier.id_reference} selected="selected"{/if}>{$carrier.name}</option>
                                    {/foreach}
                                    </select>
                                </div>
                                <label>{l s='Primary' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="checkbox" class="shipment-primary" name="shipments[{$index}][primary]" value="1"{if $shipment.primary} checked="checked"{/if}/>
                                </div>
                                <div class="col-left">
                                    <label>{l s='Type' mod='oyst'}</label>
                                    <div class="margin-form">
                                        <select name="shipments[{$index}][type]">
                                        {foreach from=$oyst.type_list key=value item=name}
                                            <option value="{$value}"{if $shipment.type == $value} selected="selected"{/if}>{$name}</option>
                                        {/foreach}
                                        </select>
                                    </div>
                                    <label>{l s='Delay' mod='oyst'}</label>
                                    <div class="margin-form">
                                        <input type="text" name="shipments[{$index}][delay]" value="{$shipment.delay}"/>
                                        <br>
                                        <span class="help-block">{l s='Values in days' mod='oyst'}</span>
                                    </div>
                                    <label>{l s='Free shipping from' mod='oyst'}</label>
                                    <div class="margin-form">
                                        <input type="text" name="shipments[{$index}][free_shipping]" value="{$shipment.free_shipping}"/>
                                    </div>
                                </div>
                                <div class="col-right">
                                    <label>{l s='Amount' mod='oyst'}</label>
                                    <div class="margin-form">
                                        <input type="text" name="shipments[{$index}][amount_leader]" value="{$shipment.amount_leader}"/>
                                        <br>
                                        <span class="help-block">{l s='First product' mod='oyst'}</span>
                                    </div>
                                    <div class="margin-form">
                                        <input type="text" name="shipments[{$index}][amount_follower]" value="{$shipment.amount_follower}"/>
                                        <br>
                                        <span class="help-block">{l s='Additionnal product' mod='oyst'}</span>
                                    </div>
                                </div>
                                <label></label>
                                <div class="margin-form">
                                    <button type="button" class="delete-shipment">{l s='Delete Shipment' mod='oyst'}</button>
                                </div>
                            </div>
                            {/foreach}
                        </div>
                        <button type="button" id="add-shipment"{if !$oyst.currentOneClickApiKeyValid} disabled="disabled"{/if}>{l s='Add Shipment' mod='oyst'}</button>
                        <p class="help-block" id="add-shipment-help" {if $oyst.currentOneClickApiKeyValid} style="display: none;"{/if}>{l s='You have to add a valid API key in order to add your shipment methods' mod='oyst'}</p>
                    </div>
                    <label>{l s='Environment' mod='oyst'}</label>
                    <div class="margin-form">
                        <select name="OYST_API_ENV_ONECLICK">
                            <option value="prod" {if $oyst.OYST_API_ENV_ONECLICK == 'prod'}selected="selected"{/if}>{l s='Production' mod='oyst'}</option>
                            <option value="preprod" {if $oyst.OYST_API_ENV_ONECLICK == 'preprod'}selected="selected"{/if}>{l s='Preproduction' mod='oyst'}</option>
                            <option value="custom" {if $oyst.OYST_API_ENV_ONECLICK == 'custom'}selected="selected"{/if}>{l s='Custom' mod='oyst'}</option>
                        </select>
                    </div>
                    <label class="env custom">{l s='Endpoint API Custom' mod='oyst'}</label>
                    <div class="margin-form env custom">
                        <input type="text" id="OYST_API_CUSTOM_ENDPOINT_ONECLCK" name="OYST_API_CUSTOM_ENDPOINT_ONECLCK" value="{$oyst.OYST_API_CUSTOM_ENDPOINT_ONECLCK|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <label class="env custom">{l s='Endpoint CDN Custom' mod='oyst'}</label>
                    <div class="margin-form env custom">
                        <input type="text" id="OYST_ONECLICK_URL_CUSTOM" name="OYST_ONECLICK_URL_CUSTOM" value="{$oyst.OYST_ONECLICK_URL_CUSTOM|escape:'htmlall':'UTF-8'}"/>
                    </div>
                </div>
                <div class="margin-form">
                    <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration">
                        {l s='Save' mod='oyst'}
                    </button>
                </div>
                <div class="margin-form">
                {if $oyst.exportRunning}
                    {l s='An export is currently running, please wait until it\'s over' mod='oyst'}
                {else}
                    <button type="submit" id="module_export_catalog_btn" name="synchronizeProducts"{if !$oyst.can_export_catalog} disabled="disabled"{/if}>
                        {if $oyst.lastExportDate}
                            {l s='Re start the export catalog' mod='oyst'}
                        {else}
                            {l s='Start the export catalog' mod='oyst'}
                        {/if}
                    </button>
                {/if}
                </div>
            </form>
        </div>
    </div>
{/if}

<div id="shipment-model" style="display: none;">
    <div class="shipment-item">
        <label>{l s='Carrier' mod='oyst'}</label>
        <div class="margin-form">
            <select name="shipments[__shipment_id__][id_carrier]">
            {foreach $oyst.carrier_list as $carrier}
                <option value="{$carrier.id_reference}">{$carrier.name}</option>
            {/foreach}
            </select>
        </div>
        <label>{l s='Primary' mod='oyst'}</label>
        <div class="margin-form">
            <input type="checkbox" class="shipment-primary" name="shipments[__shipment_id__][primary]" value="1"/>
        </div>
        <div class="col-left">
            <label>{l s='Type' mod='oyst'}</label>
            <div class="margin-form">
                <select name="shipments[__shipment_id__][type]">
                {foreach from=$oyst.type_list key=value item=name}
                    <option value="{$value}">{$name}</option>
                {/foreach}
                </select>
            </div>
            <label>{l s='Delay' mod='oyst'}</label>
            <div class="margin-form">
                <input type="text" name="shipments[__shipment_id__][delay]" value=""/>
                <br>
                <span class="help-block">{l s='Values in days' mod='oyst'}</span>
            </div>
            <label>{l s='Free shipping from' mod='oyst'}</label>
            <div class="margin-form">
                <input type="text" name="shipments[__shipment_id__][free_shipping]" value=""/>
            </div>
        </div>
        <div class="col-right">
            <label>{l s='Amount' mod='oyst'}</label>
            <div class="margin-form">
                <input type="text" name="shipments[__shipment_id__][amount_leader]" value=""/>
                <br>
                <span class="help-block">{l s='First product' mod='oyst'}</span>
            </div>
            <div class="margin-form">
                <input type="text" name="shipments[__shipment_id__][amount_follower]" value=""/>
                <br>
                <span class="help-block">{l s='Additionnal product' mod='oyst'}</span>
            </div>
        </div>
        <label></label>
        <div class="margin-form">
            <button type="button" class="delete-shipment">{l s='Delete Shipment' mod='oyst'}</button>
        </div>
    </div>
</div>

<fieldset id="logManagement">
    <legend>Logs</legend>
    <p>Display logs about any process</p>

    {if is_array($oyst.logsFile) && count($oyst.logsFile)}
        <select name="logsFile">
            <option value="">{l s='Select log' mod='oyst'}</option>
            {foreach $oyst.logsFile as $logFile}
                <option value="{$logFile}">{$logFile|substr:0:-4}</a></option>
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
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/handleAdvancedConf.js"></script>
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/handleShipment.js"></script>
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/bootstrapTab-1.5.js"></script>
<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/logManagement.js"></script>
<script>
    $(function() {
        var logManagement = new LogManagement(window.location.href);
        logManagement.initBackend();
    });
</script>
<style>

    #logManagement, #log, #logContainer, #deleteLogs {
        margin-top: 15px;
    }

    #logName {
        font-weight: bolder;
        font-size:14px;
    }

    #log {
        display: none;
        background-color: #333333;
        color: snow;
        padding: 10px;
        max-height: 400px;
        overflow: auto;
        font: message-box;
    }

    .shipment-item {
        border-bottom: 1px solid #C7D6DB;
        margin-bottom: 15px;
    }

    #shipment-model label, .shipment-item label {
        width: 150px;
    }

    #shipment-model .margin-form, .shipment-item .margin-form {
        padding-left: 160px;
    }
</style>
