<span class="delivery_label">
	{l s='Delivery' mod='legal'}:
</span>
<span class="delivery_value">
	{if $product->quantity <= 0}
		{if $allow_oosp}
			{$product->delivery_later}
		{else}
			{l s='This product is no longer in stock' mod='legal'}
		{/if}
	{else}
		{$product->delivery_now}
	{/if}
</span>				