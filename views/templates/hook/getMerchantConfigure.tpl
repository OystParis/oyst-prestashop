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

{if isset($apiError)}
    <p class="error"><strong>{l s='Got an API error:' mod='oyst'}</strong> {$apiError|escape:'htmlall':'UTF-8'}</p>
{/if}

<script>
    var notification_bo_url = "{$oyst.notification_bo_url}";
    var module_dir = "{$oyst.module_dir}";
</script>

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
                <input type="hidden" id="current_tab_value" name="current_tab" value="{$oyst.current_tab|escape:'htmlall':'UTF-8'}"/>
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
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                            <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                            {if $oyst.apikey_prod_test_error_freepay}
                            <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                            {/if}
                        </div>
                    </div>
                    <div class="env sandbox" style="display: none;">
                        <label>{l s='FreePay API Sandbox Key' mod='oyst'}</label>
                        <div class="margin-form">
                            <input type="text" id="OYST_API_SANDBOX_KEY_FREEPAY" name="OYST_API_SANDBOX_KEY_FREEPAY" value="{$oyst.OYST_API_SANDBOX_KEY_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                            <button type="submit" value="1" name="submitOystConfiguration">
                                {l s='Apply' mod='oyst'}
                            </button>
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.sandbox.oyst.eu/signup" target="_blank">backoffice.sandbox.oyst.com</a></p>
                            <p class="help-block">{l s='A problem? Go to' mod='oyst'} <a href="https://free-pay.zendesk.com/hc/fr/articles/115003312045-Comment-installer-FreePay-sur-Prestashop-" target="_blank">{l s='intallation help' mod='oyst'}</a></p>
                            {if $oyst.apikey_sandbox_test_error_freepay}
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
                            <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.staging.oyst.eu/signup" target="_blank">backoffice.staging.oyst.com</a></p>
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
                            <option value="sandbox" {if $oyst.OYST_API_ENV_FREEPAY == 'sandbox'}selected="selected"{/if}>{l s='Sandbox' mod='oyst'}</option>
                            <option value="custom" {if $oyst.OYST_API_ENV_FREEPAY == 'custom'}selected="selected"{/if}>{l s='Custom' mod='oyst'}</option>
                        </select>
                    </div>
                    <label class="env custom">{l s='Endpoint API Custom' mod='oyst'}</label>
                    <div class="margin-form env custom">
                        <input type="text" id="OYST_API_CUSTOM_ENDPOINT_FREEPAY" name="OYST_API_CUSTOM_ENDPOINT_FREEPAY" value="{$oyst.OYST_API_CUSTOM_ENDPOINT_FREEPAY|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <label>{l s='Create order before payment' mod='oyst'}</label>
                    <div class="margin-form">
                        <input type="checkbox" class="form-control" id="FC_OYST_PREORDER_FEATURE" name="FC_OYST_PREORDER_FEATURE" value="1"{if $oyst.FC_OYST_PREORDER_FEATURE} checked="checked"{/if} />
                    </div>
                    <label>{l s='State payment' mod='oyst'}</label>
                    <div class="margin-form">
                        <select id="FC_OYST_STATE_PAYMENT_FREEPAY" name="FC_OYST_STATE_PAYMENT_FREEPAY">
                            {foreach from=$oyst.order_state item=state}
                                <option value="{$state.id_order_state|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_STATE_PAYMENT_FREEPAY == $state.id_order_state} selected="selected"{/if}>{$state.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <label>{l s='Enable Fraudscoring' mod='oyst'}</label>
                    <div class="margin-form">
                        <input type="checkbox" class="form-control" id="FC_OYST_ACTIVE_FRAUD" name="FC_OYST_ACTIVE_FRAUD" value="1"{if $oyst.FC_OYST_ACTIVE_FRAUD} checked="checked"{/if} />
                    </div>
                    <div class="margin-form">
                        <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration">
                            {l s='Save' mod='oyst'}
                        </button>
                    </div>
                </div>
                <div id="tab-content-1-click" class="tab-pane">
                    <div class="productTabsSub sidebar">
                        <ul id="" class="tab">
                            <li class="tab-row"><a class="tab-page selected" href="#conf-oc">{l s='Configuration One-click' mod='oyst'}</a></li>
                            <li class="tab-row"><a class="tab-page" href="#custom-btn">{l s='Custom of button' mod='oyst'}</a></li>
                            <li class="tab-row"><a class="tab-page" href="#settings-carrier">{l s='Settings carrier' mod='oyst'}</a></li>
                            <li class="tab-row"><a class="tab-page" href="#settings-advanced">{l s='Settings advanced' mod='oyst'}</a></li>
                            <li class="tab-row"><a class="tab-page" href="#settings-restrictions">{l s='Settings restrictions' mod='oyst'}</a></li>
                            <li class="tab-row" id="tab-notification"><a class="tab-page" href="#display-notifications" >{l s='Notifications' mod='oyst'}</a></li>
                        </ul>
                    </div>
                    <div id="moduleContainer" class="tab-content-sub">
                        <div id="conf-oc" class="tab-pane-sub">
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
                                    <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                    {if $oyst.apikey_prod_test_error_oneclick}
                                    <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                    {/if}
                                </div>
                            </div>
                            <div class="env sandbox" style="display: none;">
                                <label>{l s='1-Click API Sandbox Key' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" id="OYST_API_SANDBOX_KEY_ONECLICK" name="OYST_API_SANDBOX_KEY_ONECLICK" value="{$oyst.OYST_API_SANDBOX_KEY_ONECLICK|escape:'htmlall':'UTF-8'}"/>
                                    <button type="submit" value="1" name="submitOystConfiguration">
                                        {l s='Apply' mod='oyst'}
                                    </button>
                                    <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                    {if $oyst.apikey_sandbox_test_error_oneclick}
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
                                    <p class="help-block">{l s='You don\'t have an API Key yet? Go to' mod='oyst'} <a href="https://backoffice.oyst.com/signup" target="_blank">backoffice.oyst.com</a></p>
                                    {if $oyst.apikey_custom_test_error_oneclick}
                                    <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                                    {/if}
                                </div>
                            </div>
                            <label>{l s='Environment' mod='oyst'}</label>
                            <div class="margin-form">
                                <select name="OYST_API_ENV_ONECLICK">
                                    <option value="prod" {if $oyst.OYST_API_ENV_ONECLICK == 'prod'}selected="selected"{/if}>{l s='Production' mod='oyst'}</option>
                                    <option value="sandbox" {if $oyst.OYST_API_ENV_ONECLICK == 'sandbox'}selected="selected"{/if}>{l s='Sandbox' mod='oyst'}</option>
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
                            <label>{l s='State payment' mod='oyst'}</label>
                            <div class="margin-form">
                                <select id="FC_OYST_STATE_PAYMENT_ONECLICK" name="FC_OYST_STATE_PAYMENT_ONECLICK">
                                    {foreach from=$oyst.order_state item=state}
                                        <option value="{$state.id_order_state|escape:'html':'UTF-8'}"{if $oyst.FC_OYST_STATE_PAYMENT_ONECLICK == $state.id_order_state} selected="selected"{/if}>{$state.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div id="custom-btn" class="tab-pane-sub" style="display:none;">
                            {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <label>{l s='Smart button' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="checkbox" class="form-control" name="FC_OYST_SMART_BTN" value="1"{if $oyst.FC_OYST_SMART_BTN} checked="checked"{/if} />
                                </div>
                                <label>{l s='Border rounded' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="checkbox" class="form-control" name="FC_OYST_BORDER_BTN" value="1"{if $oyst.FC_OYST_BORDER_BTN} checked="checked"{/if} />
                                </div>
                                <label>{l s='Style btn 1-Click' mod='oyst'}</label>
                                <div class="margin-form">
                                    <select name="FC_OYST_THEME_BTN">
                                        <option value="normal" {if $oyst.FC_OYST_THEME_BTN == 'normal'}selected="selected"{/if}>{l s='Normal' mod='oyst'}</option>
                                        <option value="inversed" {if $oyst.FC_OYST_THEME_BTN == 'inversed'}selected="selected"{/if}>{l s='Inversed' mod='oyst'}</option>
                                    </select>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Color' mod='oyst'}</label>
                                <div class="margin-form">
                                    <div class="input-group">
                                        <input type="color" data-hex="true" class="color mColorPickerInput mColorPicker" name="FC_OYST_COLOR_BTN"  value="{if $oyst.FC_OYST_COLOR_BTN}{$oyst.FC_OYST_COLOR_BTN|escape:'htmlall':'UTF-8'}{else}#E91E63{/if}" />
                                    </div>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Width' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_WIDTH_BTN" value="{$oyst.FC_OYST_WIDTH_BTN|escape:'htmlall':'UTF-8'}"/>
                                    <br>
                                    <span class="help-block">{l s='In pourcentage or pixel' mod='oyst'}</span>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Height' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_HEIGHT_BTN" value="{$oyst.FC_OYST_HEIGHT_BTN|escape:'htmlall':'UTF-8'}"/>
                                    <br>
                                    <span class="help-block">{l s='In pourcentage or pixel' mod='oyst'}</span>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Margin top' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_MARGIN_TOP_BTN" value="{$oyst.FC_OYST_MARGIN_TOP_BTN|escape:'htmlall':'UTF-8'}"/>
                                    <br>
                                    <span class="help-block">{l s='In pourcentage or pixel' mod='oyst'}</span>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Margin left' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_MARGIN_LEFT_BTN" value="{$oyst.FC_OYST_MARGIN_LEFT_BTN|escape:'htmlall':'UTF-8'}"/>
                                    <br>
                                    <span class="help-block">{l s='In pourcentage or pixel' mod='oyst'}</span>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Margin right' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_MARGIN_RIGHT_BTN" value="{$oyst.FC_OYST_MARGIN_RIGHT_BTN|escape:'htmlall':'UTF-8'}"/>
                                    <br>
                                    <span class="help-block">{l s='In pourcentage or pixel' mod='oyst'}</span>
                                </div>
                                <label>{l s='Position btn 1-Click' mod='oyst'}</label>
                                <div class="margin-form">
                                    <select name="FC_OYST_POSITION_BTN">
                                        <option value="before" {if $oyst.FC_OYST_POSITION_BTN == 'before'}selected="selected"{/if}>{l s='Before button add to cart' mod='oyst'}</option>
                                        <option value="after" {if $oyst.FC_OYST_POSITION_BTN == 'after'}selected="selected"{/if}>{l s='After button add to cart' mod='oyst'}</option>
                                    </select>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Id btn add to cart' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_ID_BTN_ADD_TO_CART" value="{if $oyst.FC_OYST_ID_BTN_ADD_TO_CART}{$oyst.FC_OYST_ID_BTN_ADD_TO_CART|escape:'htmlall':'UTF-8'}{else}add_to_cart{/if}"/>
                                </div>
                                <label class="control-label col-md-3 col-lg-3">{l s='Id smart btn' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" name="FC_OYST_ID_SMART_BTN" value="{if $oyst.FC_OYST_ID_SMART_BTN}{$oyst.FC_OYST_ID_SMART_BTN|escape:'htmlall':'UTF-8'}{else}#add_to_cart button{/if}"/>
                                </div>
                                <div class="margin-form">
                                    <button type="submit" value="1" name="submitOystResetCustom" class="btn btn-info module_form_reset_btn">
                                        {l s='Reset' mod='oyst'}
                                    </button>
                                </div>
                            {else}
                                <input type="hidden" name="FC_OYST_SMART_BTN" value="{$oyst.FC_OYST_SMART_BTN|intval}"/>
                                <input type="hidden" name="FC_OYST_BORDER_BTN" value="{$oyst.FC_OYST_BORDER_BTN|intval}"/>
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div id="settings-carrier" class="tab-pane-sub" style="display:none;">
                            {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <label>{l s='Carrier default' mod='oyst'}</label>
                                <div class="margin-form">
                                    <select name="FC_OYST_SHIPMENT_DEFAULT">
                                        <option value="0">{l s='Choose carrier default' mod='oyst'}</option>
                                        {foreach $oyst.carrier_list as $carrier}
                                            <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}"{if $oyst.shipment_default == $carrier.id_reference} selected="selected"{/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                {foreach $oyst.carrier_list as $carrier}
                                    <label>{$carrier.name|escape:'htmlall':'UTF-8'}</label>
                                    <div class="margin-form">
                                        <select name="FC_OYST_SHIPMENT_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
                                            <option value="0">{l s='Disabled' mod='oyst'}</option>
                                            {foreach from=$oyst.type_list key=value item=name}
                                                <option value="{$value|escape:'htmlall':'UTF-8'}" {if $value ==  Configuration::get("FC_OYST_SHIPMENT_{$carrier.id_reference}")}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <label>{l s='Delay in days' mod='oyst'}</label>
                                    <div class="margin-form">
                                        <input type="text" name="FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{Configuration::get("FC_OYST_SHIPMENT_DELAY_{$carrier.id_reference}")}{else}3{/if}" />
                                    </div>
                                {/foreach}
                            {else}
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div id="settings-advanced" class="tab-pane-sub" style="display:none;">
                            {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <label>{l s='Delay' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="text" id="FC_OYST_DELAY" name="FC_OYST_DELAY" value="{if $oyst.FC_OYST_DELAY}{$oyst.FC_OYST_DELAY|escape:'htmlall':'UTF-8'}{else}15{/if}"/>
                                </div>
                                <label>{l s='Manage quantity' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="checkbox" class="form-control" name="FC_OYST_MANAGE_QUANTITY" value="1"{if $oyst.FC_OYST_MANAGE_QUANTITY} checked="checked"{/if} />
                                </div>
                                <label>{l s='Enable advanced stock' mod='oyst'}</label>
                                <div class="margin-form">
                                    <input type="checkbox" class="form-control" name="FC_OYST_SHOULD_AS_STOCK" value="1"{if $oyst.FC_OYST_SHOULD_AS_STOCK} checked="checked"{/if} />
                                </div>
                                <div class="margin-form">
                                    <button type="submit" value="1" name="submitOystConfigurationReset" class="btn btn-info module_form_reset_btn">
                                        {l s='Reset product' mod='oyst'}
                                    </button>
                                </div>
                                <div class="margin-form">
                                    <button type="submit" value="1" name="submitOystConfigurationDisable" class="btn btn-info module_form_reset_btn">
                                        {l s='Disable product' mod='oyst'}
                                    </button>
                                </div>
                            {else}
                                <input type="hidden" name="FC_OYST_MANAGE_QUANTITY" value="{$oyst.FC_OYST_MANAGE_QUANTITY|intval}"/>
                                <input type="hidden" name="FC_OYST_SHOULD_AS_STOCK" value="{$oyst.FC_OYST_SHOULD_AS_STOCK|intval}"/>
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div id="settings-restrictions" class="tab-pane-sub" style="display:none;">
                            {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <label>{l s='Restrictions of languages' mod='oyst'}</label>
                                <div class="margin-form">
                                    <table cellpadding="0" cellspacing="0" class="table">
                                        <tbody>
                                            {foreach $oyst.languages as $lang}
                                                <tr>
                                                    <td>{$lang['name']}</td>
                                                    <td class="text-center"><input style="margin-top:0;" name="oyst_lang[]" value="{$lang['id_lang']}" {if in_array($lang['id_lang'], $oyst.restriction_languages)}checked="checked"{/if} type="checkbox"></td>
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {else}
                                {foreach $oyst.languages as $lang}
                                    <input style="display:none;" name="oyst_lang[]" value="{$lang['id_lang']}" {if in_array($lang['id_lang'], $oyst.restriction_languages)}checked="checked"{/if} type="checkbox">
                                {/foreach}
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div id="display-notifications" class="tab-pane-sub" style="display:none;">
						    {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <div>
                                    <label>Tables</label>
                                    <div class="margin-form">
                                        <select name="table_selector" id="table-selector">
                                            {foreach from=$oyst.notification_tables item=notification_table}
                                                <option value="{$notification_table}">{$notification_table}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <table id="notification-table" class="display nowrap" cellspacing="0" width="100%"></table>
                            {else}
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div id="display-notifications" class="tab-pane-sub" style="display:none;">
						    {if $oyst.OYST_ONE_CLICK_FEATURE_STATE && $oyst.currentOneClickApiKeyValid}
                                <div>
                                    <label>Tables</label>
                                    <div class="margin-form">
                                        <select name="table_selector" id="table-selector">
                                            {foreach from=$oyst.notification_tables item=notification_table}
                                                <option value="{$notification_table}">{$notification_table}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <table id="notification-table" class="display nowrap" cellspacing="0" width="100%"></table>
                            {else}
                                <div class="warn">
                                    <ul>
                                        <li>{l s='1-Click is disabled. Or 1-Click isn\'t configured.' mod='oyst'}</li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                        <div class="margin-form">
                            <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration">
                                {l s='Save' mod='oyst'}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
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

    $(function() {
        var logManagement = new LogManagement(window.location.href);
        logManagement.initBackend();
    });
</script>
