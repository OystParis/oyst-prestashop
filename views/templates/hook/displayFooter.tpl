<script>
    window.__OYST__ = window.__OYST__ || {};
    window.__OYST__.redirectUrl = '{$redirect_url}';
    window.__OYST__.baseUrl = '{$base_url}';
    window.__OYST__.cartUrl = '{$cart_url}';
    window.__OYST__.pageName = '{$page_name_oyst}';
    window.__OYST__.formSelector = '{$form_selector}';
    window.__OYST__.successRedirectUrl = '{$redirect_url}';
    window.__OYST__.failureRedirectUrl = '{$base_url}';
    {if !empty($tracking_parameters)}
    window.__OYST__.tracking = {$tracking_parameters};
    {/if}
</script>
{$script_tag nofilter}

