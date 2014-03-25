{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
	<br><span class="tax_info">{if $priceDisplay == 1}{l s='tax excl.' mod='legal'}{else}{l s='tax incl.' mod='legal'}{/if}</span>
{/if}
<span class="shipping_info">
	{if $cms_id_shipping}<a href="{$link->getCMSLink($cms_id_shipping)}">{l s='excl. shipping' mod='legal'}</a>{else}{l s='excl. shipping' mod='legal'}{/if}
</span>