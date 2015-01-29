{**
* EU Legal - Better security for German and EU merchants.
*
* @version   : 1.0.4
* @date      : 2014 08 26
* @author    : Markus Engel/Chris Gurk @ Onlineshop-Module.de | George June/Alexey Dermenzhy @ Silbersaiten.de
* @copyright : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
* @contact   : info@onlineshop-module.de | info@silbersaiten.de
* @homepage  : www.onlineshop-module.de | www.silbersaiten.de
* @license   : http://opensource.org/licenses/osl-3.0.php
* @changelog : see changelog.txt
* @compatibility : PS == 1.6.0.9
*}

		<button {if !$opc}data-show-if-js style="display:none"{/if} id="confirmOrder" onclick="javascript:legal.confirmOrder()" type="button" class="button btn btn-default standard-checkout button-medium"><span>{l s='Order With Obligation To Pay' mod='eu_legal'}</span></button>
		{if !$opc}
			<label
				data-hide-if-js
				{if $payment_option}for="submit_{$payment_option}"{else}disabled{/if} 
				id="confirmOrder" 
				class="btn btn-success btn-lg"
			>{l s='Order With Obligation To Pay' mod='eu_legal'}</label>
		{/if}
