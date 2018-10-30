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
                <div class="col-md-8 col-lg-8">
                    <div class="input-group" style="width:60%">
                        <input type="text" id="oyst_api_key" name="oyst_api_key" value="{$oyst_api_key}" readonly/>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_merchant_id">{l s='Merchant id' mod='oyst'}</label>
                <div class="col-md-8 col-lg-8">
                    <div class="input-group" style="width:60%">
                        <input type="text" id="oyst_merchant_id" name="oyst_merchant_id" value="{$oyst_merchant_id}"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_script_tag">{l s='Script tag' mod='oyst'}</label>
                <div class="col-md-8 col-lg-8">
                    <div class="input-group" style="width:60%">
                        <textarea id="oyst_script_tag" name="oyst_script_tag" rows="6">{$oyst_script_tag}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group clearfix">
                <label class="control-label col-md-4 col-lg-4" for="oyst_public_endpoints">{l s='Public endpoint' mod='oyst'}</label>
                <div class="col-md-8 col-lg-8">
                    <div class="input-group" style="width:60%">
                        <textarea id="oyst_public_endpoints" name="oyst_public_endpoints" rows="6">{$oyst_public_endpoints}</textarea>
                    </div>
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
