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
<form id="module_form" class="defaultForm form-horizontal" method="POST" action="">
    <div align="center" style="font-size: 16px;">
        <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
        <p><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
    </div>
    {if $oyst.FC_OYST_GUEST}
    <div class="text-center">
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
    <div class="panel" class="oyst_fieldset">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Configuration' mod='oyst'}
        </div>
        <div class="oyst-admin-tab">
            <fieldset>
                <legend>{l s='API Key' mod='oyst'}</legend>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-3 ">{l s='API Key' mod='oyst'}</label>
                    <div class="col-lg-9">
                        <input type="text" id="FC_OYST_API_KEY" name="FC_OYST_API_KEY" value="{$oyst.FC_OYST_API_KEY|escape:'htmlall':'UTF-8'}" />
                        <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://admin.free-pay.com/signup" target="_blank">admin.free-pay.com</a></p>
                        {if isset($oyst.oyst_connection_test.result)}
                            {if $oyst.oyst_connection_test.result}
                                <div class="alert alert-success">{l s='Your key is valid!' mod='oyst'}</div>
                            {else}
                                <div class="alert alert-danger">
                                    {l s='Your key seems invalid!' mod='oyst'}
                                    <br>
                                    <input type="checkbox" id="oyst_connection_debug" name="oyst_connection_debug" value="1"{if $smarty.post.oyst_connection_debug} checked="checked"{/if} /> Debug
                                    {if isset($smarty.post.oyst_connection_debug) && $smarty.post.oyst_connection_debug}
                                        <br><pre>{$oyst.oyst_connection_test.values|print_r|FroggyDisplaySafeHtml}</pre>
                                    {/if}
                                </div>
                            {/if}
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix advancedOptions hide">
                    <label class="control-label col-lg-3 ">{l s='Set the Oyst check endpoint' mod='oyst'}</label>
                    <div class="col-lg-9">
                        <input type="text" id="FC_OYST_API_CHECK_ENDPOINT" name="FC_OYST_API_CHECK_ENDPOINT" value="{$oyst.FC_OYST_API_CHECK_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>{l s='Payment feature' mod='oyst'}</legend>
                <div class="oyst-admin-tab">
                    <div class="form-group clearfix">
                        <label class="control-label col-lg-3 ">{l s='Enable payment feature on your website' mod='oyst'}</label>
                        <div class="col-lg-9" style="height: 31px;">
                            <label>
                                <input type="checkbox" class="form-control" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
                            </label>
                        </div>
                    </div>
                    <div class="form-group clearfix advancedOptions hide">
                        <label class="control-label col-lg-3 ">{l s='Set the Oyst payment endpoint' mod='oyst'}</label>
                        <div class="col-lg-9">
                            <input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='oyst'}
            </button>
            <button type="button" class="btn btn-default pull-right" onclick="$('.advancedOptions').toggleClass('hide');$('i', this).toggleClass('icon-eye').toggleClass('icon-eye-close');$('span', this).toggleClass('hide')">
                <i class="process-icon- icon-eye"></i> <span>{l s='Show advanced options' mod='oyst'}</span><span class="hide">{l s='Hide advanced options' mod='oyst'}</span>
            </button>
            <a class="btn btn-default pull-right" href="{$configureLink|cat:'&go_to_form=1'|escape:'htmlall':'UTF-8' }">
                <i class="process-icon- icon-key"></i>
                {l s='Get an API Key' mod='oyst'}
            </a>
        </div>
    </div>
</form>
{/if}