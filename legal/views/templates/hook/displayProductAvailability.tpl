<span class="delivery-info">
	<span class="delivery-label">
		{l s='Delivery' mod='legal'}:
	</span>
	<span class="delivery-value">
		{if $product.quantity <= 0}
			{if $product.allow_oosp}
				{$product.delivery_later}
			{else}
				{l s='This product is no longer in stock' mod='legal'}
			{/if}
		{else}
			{$product.delivery_now}
		{/if}
	</span>				
</span>				