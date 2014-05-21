{if !$priceDisplay || $priceDisplay == 2}
	{assign var='productPrice' value=$product.price}
{elseif $priceDisplay == 1}
	{assign var='productPrice' value=$product.price_tax_exc}
{/if}

{if $template_type == 'price'}

	<span class="tax-shipping-info">
		{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
		<span class="tax_info">
			{if $priceDisplay == 1}{l s='tax excl.' mod='legal'}{else}{l s='tax incl.' mod='legal'}{/if}
		</span>
		{/if}
		<span class="shipping_info">
			{if $cms_id_shipping}<a href="{$link->getCMSLink($cms_id_shipping)}">{l s='excl. shipping' mod='legal'}</a>{else}{l s='excl. shipping' mod='legal'}{/if}
		</span>
	</span>

{elseif $template_type == 'old_price'}
	
	<span class="old-price">
		<span class="old-price-label">
			{l s='previously' mod='legal'}
		</span>
		<span class="old-price-display">
			{displayWtPrice p=$product.price_without_reduction}
		</span>
		{if $product.specific_prices.reduction_type == 'percentage'}
			<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
		{/if}
	</span>
	
{elseif $template_type == 'unit_price'}
	
	{if !empty($product.unity) && $product.unit_price_ratio > 0.000000}
		{if $php_self == 'product'}
			<span class="unit-price-label">{l s='unit price' mod='legal'}:</span>
		{else}
			{math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product.unit_price_ratio assign=unit_price}
			<span class="unit-price">
				<span class="unit-price-label">{l s='unit price' mod='legal'}:</span>
				<span id="unit-price-display">{convertPrice price=$unit_price}</span> {l s='per'} {$product.unity|escape:'html':'UTF-8'}
			</span>
		{/if}
	{/if}
{/if}