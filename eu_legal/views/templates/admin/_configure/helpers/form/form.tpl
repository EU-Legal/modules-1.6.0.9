{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'checkbox_module'}
		{foreach $input.values.query as $value}
			{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
			<div class="checkbox">
				<label for="{$id_checkbox}">
					<input type="checkbox"
						name="{$input.name}[]"
						id="{$id_checkbox}"
						class="{if isset($input.class)}{$input.class}{/if}"
						{if isset($value.val)}value="{$value.val|escape:'html':'UTF-8'}"{/if}
						{if isset($value[$input.values.disabled]) and $value[$input.values.disabled]}checked="checked"{/if}
						{if isset($value[$input.values.disabled]) and $value[$input.values.disabled]}disabled="disabled"{/if} />
					{$value[$input.values.name]}
				</label>
			</div>
		{/foreach}
	{elseif $input.type == 'hr'}
		<hr>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}