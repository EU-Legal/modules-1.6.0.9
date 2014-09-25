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
<ul>
{foreach from=$virtualProducts item=product}
	<li>
		<a href="{$product.link|escape:'htmlall'}">{$product.name|escape:'htmlall'}</a>
		{if isset($product.deadline)}
			expires on {$product.deadline|escape:'htmlall'}
		{/if}
		{if isset($product.downloadable)}
			downloadable {$product.downloadable|escape:'htmlall'} time(s)
		{/if}
	</li>
{/foreach}
</ul>