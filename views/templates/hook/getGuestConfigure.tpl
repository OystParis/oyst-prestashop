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

{if !$oyst.allow_url_fopen_check}
    <p class="error"><strong>{l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}</strong></p>
{/if}
{if !$oyst.curl_check}
    <p class="error"><strong>{l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}</strong></p>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}
    <form id="module_form" class="defaultForm form-horizontal oyst contact" method="POST" action="{$oyst.configureLink|escape:'htmlall':'UTF-8'}">
        <div class="header">
            <p><img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
        </div>
        <div class="header">
            <h2>{l s='Congratulations!' mod='oyst'}</h2>
            <p style="font-size: 13px;font-weight: bold;">{l s='To enable the FreePay plugin, please fill the following form.' mod='oyst'}</p>
        </div>
        <fieldset class="panel oyst_fieldset">
            <div id="form">
                {if isset($smarty.post.form_get_apikey_submit) && ($oyst.form_get_apikey_name_error !== '' || $oyst.form_get_apikey_phone_error !== '' || $oyst.form_get_apikey_email_error !== '')}
                <div class="margin-form error" style="width: 350px;margin: auto;margin-bottom: 10px;">
                    <span style="float:right">
                        <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png"></a>
                    </span>
                    {l s='Oups!' mod='oyst'}
                </div>
                <div class="clear"></div>
                {/if}
                {if isset($smarty.post.form_get_apikey_submit) && $oyst.form_get_apikey_notify_error !== ''}
                <div class="margin-form error" style="width: 350px;margin: auto;margin-bottom: 10px;">
                    <span style="float:right">
                        <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png"></a>
                    </span>
                    {l s='Oups! An error occured while sending your information to FreePay.' mod='oyst'}
                </div>
                <div class="clear"></div>
                {/if}

                <label>{l s='Name' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_name" name="form_get_apikey_name" value="{$oyst.form_get_apikey_name|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $oyst.form_get_apikey_name_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$oyst.form_get_apikey_name_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <label>{l s='Phone' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_phone" name="form_get_apikey_phone" value="{$oyst.form_get_apikey_phone|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $oyst.form_get_apikey_phone_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$oyst.form_get_apikey_phone_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <label>{l s='Email' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_email" name="form_get_apikey_email" value="{$oyst.form_get_apikey_email|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $oyst.form_get_apikey_email_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$oyst.form_get_apikey_email_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <label>{l s='Nb transactions by month' mod='oyst'}</label>
                <div class="margin-form">
                    <input type="text" id="form_get_apikey_transac" name="form_get_apikey_transac" value="{$oyst.form_get_apikey_transac|escape:'html':'UTF-8'}"/>
                    {if isset($smarty.post.form_get_apikey_submit) && $oyst.form_get_apikey_transac_error !== ''}
                    <div style="color: #CC0000;margin-top: 3px;">{$oyst.form_get_apikey_transac_error|escape:'html':'UTF-8'}</div>
                    {/if}
                </div>

                <div class="margin-form" style="width: 261px;">
                    <button type="submit" value="1" id="form_get_apikey_submit" name="form_get_apikey_submit" class="btn btn-info form-control bigger-">
                        <strong>{l s='Get my API Key' mod='oyst'}</strong>
                    </button>
                </div>

                <div class="margin-form" style="width: 261px;text-align: center;"><p>{l s='OR' mod='oyst'}</p></div>
                <div class="margin-form" style="width: 261px;text-align: center;padding-bottom: 0;">
                    <a href="{$oyst.configureLink|cat:'&go_to_conf=1'|escape:'htmlall':'UTF-8' }" style="text-decoration: underline;">
                        {l s='I have an API Key' mod='oyst'}
                    </a>
                </div>
            </div>
        </fieldset>
    </form>
{/if}
