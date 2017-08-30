<div id="product-btn-oneclick" class="panel product-tab">
	<!-- <input name="submitted_tabs[]" value="Morpho" type="hidden"> -->
	<h3>{l s='Display button OneClick' mod='freepay'}</h3>
	<div class="form-group">
		<!-- <div class="col-lg-1">
			<span class="pull-right">
				{include file="controllers/products/multishop/checkbox.tpl" field="active_oneclick" type="radio" onclick=""}
			</span>
		</div> -->
		{var_dump()}
		<label class="control-label col-lg-3">
			{l s='Button OneClick' mod='freepay'}
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="active_oneclick" id="active_oneclick_on" value="1" {if $oyst.active_oneclick || !$oyst.product_is_associated_to_shop}checked="checked" {/if} />
				<label for="active_oneclick_on" class="radioCheck">
					{l s='Yes' mod='freepay'}
				</label>
				<input type="radio" name="active_oneclick" id="active_oneclick_off" value="0" {if !$oyst.active_oneclick && $oyst.product_is_associated_to_shop}checked="checked"{/if} />
				<label for="active_oneclick_off" class="radioCheck">
					{l s='No' mod='freepay'}
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
