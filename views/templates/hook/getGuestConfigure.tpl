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
        <p align="center">
            <img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /><br>
            Module version : {$oyst.module_version}
        </p>
    </form>
{/if}
