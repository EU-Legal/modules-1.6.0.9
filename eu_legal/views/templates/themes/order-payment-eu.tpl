<style>
	table.payment-summary tbody td
	{
		border: 0px;
	}
	table.payment-summary tbody
	{
		border: 0px;
	}
	table.payment-summary tbody.payment-option
	{
		border: 1px solid #CCC;
	}
</style>

<div class="panel">
	<h2>{l s='Payment Options'}</h2>
	{if $payment_options}
		<table class="table payment-summary">
			{foreach from=$payment_options item=option key=payment_name}
				<tbody class="payment-option" id="{$payment_name}_payment">
					<tr>
						<td>
							<!-- Hide checkbox, we'll display it only if JS is available -->
							<input {if !$opc}data-show-if-js style="display:none;"{/if} name="payment_option" id="choose_{$payment_name}" type="radio" value="{$payment_name}">
							<!-- Show a button to select payment method if there is no JS -->
							{if !$opc}
								<div data-hide-if-js>
									{if $payment_name !== $payment_option}
										<form action="">
											<input type="hidden" name="payment_option" value="{$payment_name}">
											<input type="hidden" name="step" value="3">
											<button class="btn btn-default">{l s='Choose'}</button>
										</form>
									{else}
										<span class="label label-success">{l s='Chosen'}</span>
									{/if}
								</div>
							{/if}
						</td>
						<td><label for="choose_{$payment_name}"><img src="{$option.logo}" alt=""></label></td>
						<td><label for="choose_{$payment_name}">{$option['cta_text']}</label></td>
					</tr>
					<tr {if $payment_name !== $payment_option or !isset($option.form)}style="display:none"{/if} id="{$payment_name}_form_container" data-do-not-toggle="{if isset($option.form)}0{else}1{/if}">
						<td colspan="3">
							{if isset($option.form)}
								{$option.form}
							{else}
								<form method="{if isset($option.method)}{$option.method}{else}POST{/if}" action="{$option.action}">
									{if isset($option.inputs)}
										{foreach from=$option.inputs item=value key=name}
											<input type="hidden" name="{$name}" value="{$value}">
										{/foreach}
									{/if}
									<input style='display:none' type='submit' id='submit_{$payment_name}'>
								</form>
							{/if}
						</td>
					</tr>
				</tbody>
			{/foreach}
		</table>
	{else}
		<p class="alert alert-warning">{l s='No payment modules have been installed.'}</p>
	{/if}
</div>
