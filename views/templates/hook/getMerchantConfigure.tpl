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

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
    <form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="#">
        <div class="header">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
            <p class="sub-header"><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
        </div>
        {if $oyst.FC_OYST_GUEST && $phone}
            <div class="header">
            <p>{$message} <strong>{$phone}</strong></p>

            <p><a href="{$configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8'}">{l s='Change your phone number' mod='oyst'}</a></p>
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
            <label>{l s='API Key' mod='oyst'}</label>
            <div class="margin-form">
                <input type="text" id="FC_OYST_API_KEY" name="FC_OYST_API_KEY" value="{$oyst.FC_OYST_API_KEY|escape:'htmlall':'UTF-8'}"/>
                <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                {if $oyst.oyst_apiKey_test_error}
                <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                {/if}
            </div>

            <label>{l s='Enable FreePay' mod='oyst'}</label>
            <div class="margin-form">
                <input type="checkbox" class="form-control" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
            </div>
            <label class="advancedOptions">{l s='Set the Oyst payment endpoint' mod='oyst'}</label>
            <div class="margin-form advancedOptions">
                <input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}"/>
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
                    <a href="{$configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8' }" style="text-decoration: underline;">
                        <img src="../img/t/AdminAdmin.gif" alt="" style="vertical-align: text-bottom;">{l s='Get an API Key' mod='oyst'}
                    </a>
                </p>
            </div>
        </fieldset>
    </form>
{/if}

<script type="text/javascript" src="{$oyst.module_dir|escape:'html':'UTF-8'}views/js/handleAdvancedConf.js"></script>
