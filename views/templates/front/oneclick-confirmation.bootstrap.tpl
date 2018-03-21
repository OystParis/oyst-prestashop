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

{capture name=path}{l s='Order confirmation' mod='oyst'}{/capture}

<h1 class="page-heading">{l s='Order confirmation' mod='oyst'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}
<p class="alert alert-success">{l s='Your order on %s is complete.' sprintf=$shop_name mod='oyst'}</p>

<div class="box">
    <p>{l s='Your order ID is:' mod='oyst'} <strong>{$reference_order|escape:'htmlall':'UTF-8'}</strong> . {l s='Your order ID has been sent via email.' mod='oyst'}</p>
</div>
<p class="cart_navigation exclusive">
	<a class="button-exclusive btn btn-default" href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}" title="{l s='Go to your order history page' mod='oyst'}"><i class="icon-chevron-left"></i>{l s='View your order history' mod='oyst'}</a>
</p>
