{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'checkbox_module'}
		<table class="table">
		{foreach $input.values.query as $value}
			{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
			<tr>
				<td width="30">
					<input type="checkbox"
						name="{$input.name}[]"
						id="{$id_checkbox}"
						class="{if isset($input.class)}{$input.class}{/if}"
						{if isset($value.val)}value="{$value.val|escape:'html':'UTF-8'}"{/if} />
				</td>
				<td width="200">
					{$value[$input.values.name]}
				</td>
				<td>
					{if isset($value[$input.values.disabled]) and $value[$input.values.disabled]}
						{if $value.eu_module}
							<span class="label label-success">{l s='Already installed'}</span>
						{else}
							<span class="label label-warning">{l s='Original installed'}</span>
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