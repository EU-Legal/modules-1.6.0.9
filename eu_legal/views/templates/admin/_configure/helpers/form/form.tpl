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

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'checkbox_module'}
		<table class="table">
		{foreach $input.values.query as $value}
			{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
			<tr>
				<td width="30">
					<input type="checkbox"
						name="{$input.name|escape:'htmlall'}[]"
						id="{$id_checkbox|escape:'htmlall'}"
						class="{if isset($input.class)}{$input.class}{/if}"
						{if isset($value.val)}value="{$value.val|escape:'html':'UTF-8'}"{/if} />
				</td>
				<td width="200">
					{$value[$input.values.name]|escape:'htmlall'}
				</td>
				<td>
					{if isset($value[$input.values.disabled]) and $value[$input.values.disabled]}
						{if $value.eu_module}
							<span class="label label-success">{l s='Already installed' mod='eu_legal'}</span>
						{else}
							<span class="label label-warning">{l s='Original installed' mod='eu_legal'}</span>
						{/if}
					{/if}
				</td>
			</tr>
		{/foreach}
		</table>
	{elseif $input.type == 'hr'}
		<hr>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}