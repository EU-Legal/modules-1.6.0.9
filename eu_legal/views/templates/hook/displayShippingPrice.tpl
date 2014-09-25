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
{displayPrice price=$shipping_price}<br> {if $no_address_selected}{l s='Shipping to' mod='eu_legal'} {$default_country}{if $shipping_link}<br><a href="{$shipping_link}" class="iframe">{l s='further shipping costs' mod='eu_legal'}</a>{else}<br>{l s='further shipping costs' mod='eu_legal'}{/if}{/if}
