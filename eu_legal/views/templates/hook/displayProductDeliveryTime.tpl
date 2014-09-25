{**
* EU Legal - Better security for German and EU merchants.
*
* @version   : 1.0.2
* @date      : 2014 08 26
* @author    : Markus Engel/Chris Gurk @ Onlineshop-Module.de | George June/Alexey Dermenzhy @ Silbersaiten.de
* @copyright : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
* @contact   : info@onlineshop-module.de | info@silbersaiten.de
* @homepage  : www.onlineshop-module.de | www.silbersaiten.de
* @license   : http://opensource.org/licenses/osl-3.0.php
* @changelog : see changelog.txt
* @compatibility : PS == 1.6.0.9
*}

{if $is_object}
<span class="delivery-info">
	<span class="delivery-label">
		{l s='Delivery' mod='eu_legal'}:
	</span>
	<span class="delivery-value">
		{if $product->quantity <= 0}
			{if $allow_oosp}
				{$product->delivery_later|escape:'htmlall'}
			{else}
				{l s='This product is no longer in stock' mod='eu_legal'}
			{/if}
		{else}
			{$product->delivery_now|escape:'htmlall'}
		{/if}
	</span>				
</span>
{else}
<span class="delivery-info">
	<span class="delivery-label">
		{l s='Delivery' mod='eu_legal'}:
	</span>
	<span class="delivery-value">
		{if $product.quantity <= 0}
			{if $product.allow_oosp}
				{$product.delivery_later|escape:'htmlall'}
			{else}
				{l s='This product is no longer in stock' mod='eu_legal'}
			{/if}
		{else}
			{$product.delivery_now|escape:'htmlall'}
		{/if}
	</span>				
</span>
{/if}		
