{if isset($result) && $result == 'ok'}
<div class="bootstrap">
    <div class="alert alert-success">
        <button data-dismiss="alert" class="close" type="button">×</button>
        {l s='The new configuration has been saved!' mod='oyst'}
    </div>
</div>
{/if}

<form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="">
    <div align="center" style="font-size: 16px;">
        <p><img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
    </div>
    <div class="oyst-admin-tab tab-content">
        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_api_key">{l s='Access Token' mod='oyst'}</label>
                <div class="col-md-5 col-lg-5">
                    <div class="input-group" style="width:100%">
                        <input type="text" id="oyst_api_key" name="oyst_api_key" value="{$oyst_api_key}" readonly/>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_merchant_id">{l s='Merchant id' mod='oyst'}</label>
                <div class="col-md-5 col-lg-5">
                    <div class="input-group" style="width:100%">
                        <input type="text" id="oyst_merchant_id" name="oyst_merchant_id" value="{$oyst_merchant_id}"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_script_tag">{l s='Script tag' mod='oyst'}</label>
                <div class="col-md-5 col-lg-5">
                    <div class="input-group" style="width:100%">
                        <textarea id="oyst_script_tag" name="oyst_script_tag" rows="6">{$oyst_script_tag}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_public_endpoints">{l s='Public endpoint' mod='oyst'}</label>
                <div class="col-md-5 col-lg-5">
                    <div class="input-group" style="width:100%">
                        <textarea id="oyst_public_endpoints" name="oyst_public_endpoints" rows="6">{$oyst_public_endpoints}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group clearfix">
            <label class="control-label col-md-4 col-lg-4">{l s='Statut à la création des commandes' mod='oyst'}</label>
            <div class="col-md-5 col-lg-5">
                <select name="oyst_order_creation_status">
                    {foreach from=$order_states item=order_state}
                        <option value="{$order_state.id_order_state}" {if $oyst_order_creation_status == $order_state.id_order_state}selected="selected"{/if}>{$order_state.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4">{l s='Masquer les erreurs' mod='oyst'}</label>
                <div class="col-md-8 col-lg-8" style="height: 31px;">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="oyst_hide_errors" id="oyst_hide_errors_on" value="1" {if $oyst_hide_errors == 1} checked="checked"{/if}>
                        <label for="oyst_hide_errors_on" class="radioCheck">
                            {l s='Oui' mod='oyst'}
                        </label>
                        <input type="radio" name="oyst_hide_errors" id="oyst_hide_errors_off" value="0" {if $oyst_hide_errors == 0} checked="checked"{/if}>
                        <label for="oyst_hide_errors_off" class="radioCheck">
                            {l s='Non' mod='oyst'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix" style="text-align: center;">
                <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-info">
                    <strong>{l s='Save' mod='oyst'}</strong>
                </button>
            </div>
        </div>
    </div>
</form>
