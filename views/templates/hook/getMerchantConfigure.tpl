{if isset($result) && $result == 'ok'}
    <p class="conf"><strong>{l s='The new configuration has been saved!' mod='oyst'}</strong></p>
{/if}

<form id="module_form" class="defaultForm form-horizontal oyst configuration" method="POST" action="">
    <div class="header">
        <p><img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" style="height: 100px;"/></p>
    </div>
    <div class="oyst-admin-tab tab-content">
        <label for="oyst_api_key">{l s='Access Token' mod='oyst'}</label>
        <div class="margin-form">
            <input type="text" class="form-control" id="oyst_api_key" name="oyst_api_key" value="{$oyst_api_key}" readonly/>
        </div>

        <label for="oyst_merchant_id">{l s='Merchant id' mod='oyst'}</label>
        <div class="margin-form">
            <input type="text" class="form-control" id="oyst_merchant_id" name="oyst_merchant_id" value="{$oyst_merchant_id}"/>
        </div>

        <label for="oyst_script_tag">{l s='Script tag' mod='oyst'}</label>
        <div class="margin-form">
            <textarea id="oyst_script_tag" name="oyst_script_tag" rows="6">{$oyst_script_tag}</textarea>
        </div>

        <label for="oyst_public_endpoints">{l s='Public endpoint' mod='oyst'}</label>
        <div class="margin-form">
            <textarea id="oyst_public_endpoints" name="oyst_public_endpoints" rows="6">{$oyst_public_endpoints}</textarea>
        </div>

        <div class="margin-form">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration">
                {l s='Save' mod='oyst'}
            </button>
        </div>
    </div>
</form>
