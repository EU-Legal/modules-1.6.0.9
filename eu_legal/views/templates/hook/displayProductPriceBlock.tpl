{if $is_object}
	{if !$priceDisplay || $priceDisplay == 2}
		{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
	{elseif $priceDisplay == 1}
		{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
	{/if}
{else}
	{if !$priceDisplay || $priceDisplay == 2}
		{assign var='productPrice' value=$product.price}
	{elseif $priceDisplay == 1}
		{assign var='productPrice' value=$product.price_tax_exc}
	{/if}
	
	{assign var='productPriceWithoutReduction' value=$product.price_without_reduction}
	
{/if}

{if $template_type == 'price'}
	
	{if $is_object}
		<span class="tax-shipping-info eu-legal">
			{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
			<span class="tax_info">
				{if $priceDisplay == 1}{l s='tax excl.' mod='eu_legal'}{else}{l s='tax incl.' mod='eu_legal'}{/if}
			</span>
			{/if}
			<span class="shipping_info">
				{if $cms_id_shipping}<a href="{$link->getCMSLink($cms_id_shipping)}">{l s='excl. shipping' mod='eu_legal'}</a>{else}{l s='excl. shipping' mod='eu_legal'}{/if}
			</span>
		</span>
	{else}
		<span class="tax-shipping-info eu-legal">
			{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
			<span class="tax_info">
				{if $priceDisplay == 1}{l s='tax excl.' mod='eu_legal'}{else}{l s='tax incl.' mod='eu_legal'}{/if}
			</span>
			{/if}
			<span class="shipping_info">
				{if $cms_id_shipping}<a href="{$link->getCMSLink($cms_id_shipping)}">{l s='excl. shipping' mod='eu_legal'}</a>{else}{l s='excl. shipping' mod='eu_legal'}{/if}
			</span>
		</span>
	{/if}
	

{elseif $template_type == 'old_price'}
	
		{if $is_object}
			<span class="old-price eu-legal">
				<span class="old-price-label">
					{l s='previously' mod='eu_legal'}
				</span>
				<span class="old-price-display">
					{displayWtPrice p=$productPriceWithoutReduction}
				</span>
			</span>
		{else}
			<span class="old-price eu-legal">
				<span class="old-price-label">
					{l s='previously' mod='eu_legal'}
				</span>
				<span class="old-price-display">
					{displayWtPrice p=$productPriceWithoutReduction}
				</span>
				{if $product.specific_prices.reduction_type == 'percentage'}
				<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
				{/if}
			</span>
		{/if}
	
{elseif $template_type == 'unit_price'}
	
	{if $is_object}
		{if !empty($product->unity) && $product->unit_price_ratio > 0.000000}
			{math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product->unit_price_ratio assign=unit_price}
			<p class="unit-price eu-legal">
				<span class="unit-price-label">{l s='unit price' mod='eu_legal'}:</span>
				<span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per' mod='eu_legal'} {$product->unity|escape:'html':'UTF-8'}
			</p>
		{/if}
	{else}
		{if !empty($product.unity) && $product.unit_price_ratio > 0.000000}
		{math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product.unit_price_ratio assign=unit_price}
		<span class="unit-price eu-legal">
			<span class="unit-price-label">{l s='unit price' mod='eu_legal'}:</span>
			<span id="unit-price-display">{convertPrice price=$unit_price}</span> {l s='per' mod='eu_legal'} {$product.unity|escape:'html':'UTF-8'}
		</span>
		{/if}
	{/if}

{elseif $template_type == 'weight'}

	{if $show_weights and ($weight > 0 or $combination_weight > 0)}
		{addJsDef product_weight=$weight}
		{if $is_object}
			<p class="weight-info eu-legal">
				<span class="weight-label">{l s='Weight' mod='eu_legal'}:</span>
				<span class="weight-display"><span class="weight-value">{($weight+$combination_weight)|round:2}</span> {$weight_unit|escape:'html':'UTF-8'}</span>
			</p>
		{else}
			<span class="weight-info eu-legal">
				<span class="weight-label">{l s='Weight' mod='eu_legal'}:</span>
				<span class="weight-display"><span class="weight-value">{($weight+$combination_weight)|round:2}</span> {$weight_unit|escape:'html':'UTF-8'}</span>
			</span>
		{/if}
		
	{/if}
{/if}

