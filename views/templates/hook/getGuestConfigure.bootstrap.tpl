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

{if isset($oyst.result) && $oyst.result eq 'ko'}
    <div class="bootstrap">
        <div class="alert alert-danger">
            <button data-dismiss="alert" class="close" type="button">×</button>
            {l s='An error occured!' mod='oyst'}
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
    <form id="form_get_apikey" class="defaultForm form-horizontal" method="POST" action="#">
        <p class="text-center">
            <img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /><br>
            Module version : {$oyst.module_version}<br>
        </p>
        <div class="text-center">
            <h2>{l s='Congratulations!' mod='oyst'}</h2>
            <p>{l s='All you have to do now is enabling FreePay plugin to benefit from' mod='oyst'} <strong>{l s='credit card payment 100% free' mod='oyst'}</strong>!</p>
        </div>
        <div class="panel oyst_fieldset">
            <div class="oyst-admin-tab">
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Name' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_name" name="form_get_apikey_name" value="" />
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Phone' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_phone" name="form_get_apikey_phone" value="" />
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Email' mod='oyst'}</label>
                    <div class="col-lg-4">
                        <input type="text" id="form_get_apikey_email" name="form_get_apikey_email" value="" />
                    </div>
                </div>
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4"></label>
                    <div class="col-lg-4">
                        <button type="submit" value="1" id="form_get_apikey_submit" name="form_get_apikey_submit" class="btn btn-info form-control bigger-">
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
