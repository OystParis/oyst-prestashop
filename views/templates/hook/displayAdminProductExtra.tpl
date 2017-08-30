<h4>{l s='Display button OneClick' mod='freepay'}</h4>
<div class="separation"></div>
<div>
	<label class="text"> {l s='Button OneClick' mod='freepay'}:</label>
	<input type="radio" name="active_oneclick" id="active_oneclick_on" value="1" {if $oyst.active_oneclick || !$oyst.product_is_associated_to_shop}checked="checked" {/if} />
	<label for="active_oneclick_on" class="radioCheck">{l s='Yes' mod='freepay'}</label>
	<input type="radio" name="active_oneclick" id="active_oneclick_off" value="0" {if !$oyst.active_oneclick && $oyst.product_is_associated_to_shop}checked="checked"{/if} />
	<label for="active_oneclick_off" class="radioCheck">{l s='No' mod='freepay'}</label>
</div>
