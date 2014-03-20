{if $page_name == 'product'}
	<span id="unit-price-pre">{l s='Unit Price' mod='gc_german'}:</span>
{else}
	{if !empty($product->unity) && $product->unit_price_ratio > 0.000000}
	{math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product->unit_price_ratio assign=unit_price}
	<p class="unit-price">
		<span id="unit-price-pre">{l s='Unit Price' mod='gc_german'}:</span> <span class="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per' mod='gc_german'} {$product->unity|escape:'html':'UTF-8'}
	</p>
	{/if}
{/if}