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
    <p class="conf"><strong>{l s='The new configuration has been saved!' mod='oyst'}</strong></p>
{/if}

{if !$oyst.allow_url_fopen_check}
    <p class="error"><strong>{l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}</strong></p>
{/if}
{if !$oyst.curl_check}
    <p class="error"><strong>{l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}</strong></p>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
    <form id="module_form" class="defaultForm form-horizontal" method="POST" action="#">
        <div align="center" style="font-size: 16px;">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
            <p><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
        </div>
        <div align="center">
            <h2>{l s='Congratulations!' mod='oyst'}</h2>
            <p>{l s='All you have to do now is enabling FreePay plugin to benefit from' mod='oyst'} <strong>{l s='credit card payment 100% free' mod='oyst'}</strong>!</p>
        </div>
        <div class="panel oyst_fieldset">
            <div class="oyst-admin-tab">
                <div class="form-group clearfix{if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_name_error !== ''} has-error{/if}">
                    <label class="control-label col-lg-4">{l s='Name' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_name" name="form_get_apikey_name" value="{$smarty.post.form_get_apikey_name}" />
                        {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_name_error !== ''}
                            <span class="help-block" style="margin-bottom: 0;">{$form_get_apikey_name_error}</span>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix{if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_phone_error !== ''} has-error{/if}">
                    <label class="control-label col-lg-4">{l s='Phone' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_phone" name="form_get_apikey_phone" value="{$smarty.post.form_get_apikey_phone}" />
                        {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_phone_error !== ''}
                            <span class="help-block" style="margin-bottom: 0;">{$form_get_apikey_phone_error}</span>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix{if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_email_error !== ''} has-error{/if}">
                    <label class="control-label col-lg-4">{l s='Email' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_email" name="form_get_apikey_email" value="{$smarty.post.form_get_apikey_email}" />
                        {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_email_error !== ''}
                            <span class="help-block" style="margin-bottom: 0;">{$form_get_apikey_email_error}</span>
                        {/if}
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4"></label>
                    <div class="col-lg-4">
                        <button type="submit" value="1" id="form_get_apikey_submit" name="form_get_apikey_submit" class="btn btn-info form-control bigger-" style="background-color: #00aff0;border-color: #008abd;">
                            <strong>{l s='Get my API Key' mod='oyst'}</strong>
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel-footer text-center">
                <a class="btn btn-default" href="{$configureLink|cat:'&go_to_conf=1'|escape:'htmlall':'UTF-8' }">
                    <i class="process-icon- icon-key"></i>
                    {l s='I have an API Key' mod='oyst'}
                </a>
            </div>
        </div>
    </form>
{/if}
