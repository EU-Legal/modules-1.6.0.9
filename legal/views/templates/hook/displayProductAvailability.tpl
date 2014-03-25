<p id="gc_availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) || ($product->quantity > 0 && !$product->delivery_now) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
	<span id="availability_value"{if $product->quantity <= 0} class="warning_inline"{/if}>{if $product->quantity <= 0}{if $allow_oosp}{$product->delivery_later}{else}{l s='This product is no longer in stock' mod='gc_german'}{/if}{else}{$product->delivery_now}{/if}</span>				
</p>
