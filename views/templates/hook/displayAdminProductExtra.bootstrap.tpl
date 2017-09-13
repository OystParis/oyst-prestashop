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
<div id="product-btn-oneclick" class="panel product-tab">
	<h3>{l s='Display button OneClick' mod='oyst'}</h3>
	<div class="form-group">
		<label class="control-label col-lg-3">
			{l s='Button OneClick' mod='oyst'}
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="active_oneclick" id="active_oneclick_on" value="1" {if $oyst.active_oneclick || !$oyst.product_is_associated_to_shop}checked="checked" {/if} />
				<label for="active_oneclick_on" class="radioCheck">
					{l s='Yes' mod='oyst'}
				</label>
				<input type="radio" name="active_oneclick" id="active_oneclick_off" value="0" {if !$oyst.active_oneclick && $oyst.product_is_associated_to_shop}checked="checked"{/if} />
				<label for="active_oneclick_off" class="radioCheck">
					{l s='No' mod='oyst'}
				</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="panel-footer">
		<a href="index.php?controller=AdminProducts" class="btn btn-default"><i class="process-icon-cancel"></i> Annuler</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> Enregistrer</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> Enregistrer et rester</button>
	</div>
</div>
