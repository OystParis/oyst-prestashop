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
    <form id="module_form" class="defaultForm form-horizontal oyst contact" method="POST" action="#">
        <div class="header">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /></p>
            <p class="sub-header"><b>0€</b> {l s='installation fees' mod='oyst'} - <b>0%</b> {l s='transaction fees' mod='oyst'} - <b>0€</b> {l s='subscription fees' mod='oyst'}</p>
        </div>
        <div class="header">
            <h2>{l s='Congratulations!' mod='oyst'}</h2>
            <p>{l s='All you have to do now is enabling FreePay plugin to benefit from' mod='oyst'} <strong>{l s='credit card payment 100&#37 free' mod='oyst'}</strong>!</p>
        </div>
        <fieldset class="panel oyst_fieldset">
            <div id="form">
                {if isset($smarty.post.form_get_apikey_submit) && ($form_get_apikey_name_error !== '' || $form_get_apikey_phone_error !== '' || $form_get_apikey_email_error !== '')}
                <div class="margin-form error" style="width: 350px;margin: auto;margin-bottom: 10px;">
                    <span style="float:right">
                        <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png"></a>
                    </span>
                    {l s='Oups!' mod='oyst'}
                </div>
                <div class="clear"></div>
                {/if}

                <label>{l s='Name' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_name" name="form_get_apikey_name" value="{$smarty.post.form_get_apikey_name|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_name_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$form_get_apikey_name_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <label>{l s='Phone' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_phone" name="form_get_apikey_phone" value="{$smarty.post.form_get_apikey_phone|escape:'html':'UTF-8'}" />
                    {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_phone_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$form_get_apikey_phone_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <label>{l s='Email' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_email" name="form_get_apikey_email" value="{$smarty.post.form_get_apikey_email|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $form_get_apikey_email_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$form_get_apikey_email_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <div class="margin-form" style="width: 261px;">
                    <button type="submit" value="1" id="form_get_apikey_submit" name="form_get_apikey_submit" class="btn btn-info form-control bigger-">
                        <strong>{l s='Get my API Key' mod='oyst'}</strong>
                    </button>
                </div>

                <div class="margin-form" style="width: 261px;text-align: center;"><p>{l s='OR' mod='oyst'}</p></div>
                <div class="margin-form" style="width: 261px;text-align: center;padding-bottom: 0;">
                    <a href="{$configureLink|cat:'&go_to_conf=1'|escape:'htmlall':'UTF-8' }" style="text-decoration: underline;">
                        <img src="../img/t/AdminAdmin.gif" alt="" style="vertical-align: text-bottom;">{l s='I have an API Key' mod='oyst'}
                    </a>
                </div>
            </div>
        </fieldset>
    </form>
{/if}
