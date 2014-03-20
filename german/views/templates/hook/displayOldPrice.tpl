{if $page_name == 'product'}
	<span class="old_price_display_pre">{l s='previous' mod='gc_german'}</span>
	<!--span class="old_price_display_tax">{if $tax_enabled && $display_tax_label == 1}{if $priceDisplay == 1}{l s='tax excl.' mod='gc_german'}{else}{l s='tax incl.' mod='gc_german'}{/if}{/if}</span-->
{else}
	<span class="old_price_display_pre">{l s='previous' mod='gc_german'}</span>
	<!--span class="old_price_display_tax">{if $tax_enabled && $display_tax_label == 1}{if $priceDisplay == 1}{l s='tax excl.' mod='gc_german'}{else}{l s='tax incl.' mod='gc_german'}{/if}{/if}</span-->
{/if}