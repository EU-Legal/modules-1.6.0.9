<div class="row">
	<div class="col-lg-4 col-lg-offset-8 text-right">
		<button {if !$opc}data-show-if-js style="display:none"{/if} id="confirmOrder" disabled onclick="javascript:legal.confirmOrder()" type="button" class="btn btn-success btn-lg">{l s='Order With Obligation To Pay'}</button>
		{if !$opc}
			<label
				data-hide-if-js
				{if $payment_option}for="submit_{$payment_option}"{else}disabled{/if} 
				id="confirmOrder" 
				class="btn btn-success btn-lg"
			>{l s='Order With Obligation To Pay'}</label>
		{/if}
	</div>
</div>