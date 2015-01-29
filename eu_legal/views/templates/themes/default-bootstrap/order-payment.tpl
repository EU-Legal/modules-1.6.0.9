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
{if !$opc}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{addJsDefL name=txtProduct}{l s='product' mod='eu_legal' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='products' mod='eu_legal' js=1}{/addJsDefL}
	{addJsDefL name=txtTOSIsNotAccepted}{l s='The service terms have not been accepted' mod='eu_legal' js=1}{/addJsDefL}
	{addJsDefL name=txtNoPaymentMethodIsSelected}{l s='No payment method has been selected' mod='eu_legal' js=1}{/addJsDefL}
	{addJsDefL name=txtRevocationTermIsNotAccepted}{l s='The revocation terms have not been accepted' mod='eu_legal' js=1}{/addJsDefL}

	{capture name=path}{l s='Your payment method' mod='eu_legal'}{/capture}
	<h1 class="page-heading">{l s='Please choose your payment method' mod='eu_legal'}</h1>
{else}
	<h1 class="page-heading step-num"><span>3</span> {l s='Please choose your payment method' mod='eu_legal'}</h1>
{/if}

{addJsDef PS_EU_PAYMENT_API=isset($PS_EU_PAYMENT_API) && $PS_EU_PAYMENT_API}
{addJsDef is_partially_virtual=$is_partially_virtual|intval}

{if !$opc}
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}
	{include file="$tpl_dir./errors.tpl"}
{else}
	<div id="opc_payment_methods" class="opc-main-block">
		<div id="opc_payment_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}
		{if !$opc}
			{hook h='displayBeforePayment'}
		{/if}
		{if !isset($PS_EU_PAYMENT_API) or !$PS_EU_PAYMENT_API}
			{include file="$legal_theme_dir/order-summary.tpl"}
		{/if}
		<div class="paiement_block">
			<div id="HOOK_TOP_PAYMENT">{$HOOK_TOP_PAYMENT}</div>
				{if $HOOK_PAYMENT}
					{if $opc}<div id="opc_payment_methods-content">{/if}
					<div id="HOOK_PAYMENT">
						{$HOOK_PAYMENT}
					</div>
					{if $opc}</div> <!-- end opc_payment_methods-content -->{/if}
				{else}
					<p class="alert alert-warning">{l s='No payment modules have been installed.' mod='eu_legal'}</p>
				{/if}
				
				
				{if isset($PS_EU_PAYMENT_API) and $PS_EU_PAYMENT_API}
					{if ! $opc}
					{include file="$legal_theme_dir/order-address.tpl"}
					{/if}
					{if $voucherAllowed}
						<div  id="cart_voucher" class="cart_voucher">
							{if isset($errors_discount) && $errors_discount}
								<ul class="alert alert-danger">
									{foreach $errors_discount as $k=>$error}
										<li>{$error|escape:'html':'UTF-8'}</li>
									{/foreach}
								</ul>
							{/if}
							<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
								<fieldset>
									<h4>{l s='Vouchers' mod='eu_legal'}</h4>
									<input type="text" class="discount_name form-control" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
									<input type="hidden" name="submitDiscount" />
									<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='OK' mod='eu_legal'}</span></button>
								</fieldset>
							</form>
							{if $displayVouchers}
								<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:' mod='eu_legal'}</p>
								<div id="display_cart_vouchers">
									{foreach $displayVouchers as $voucher}
										{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
									{/foreach}
								</div>
							{/if}
						</div>
					{/if}

					<div {if !$opc}style="display:none" data-show-if-js{/if} class="checkbox_conditions box" id="tos">
						<h3 class="page-subheading">{l s='Terms of service' mod='eu_legal'}</h3>
						<p class="checkbox checkbox_conditions">
							{if isset($conditions) && $conditions}
							<input type="checkbox" name="cgv" id="cgv-legal" value="1"/>
							{/if}
							{if isset($PS_CONDITIONS_CMS_ID) && $PS_CONDITIONS_CMS_ID}
							   <label for="cgv-legal">{l s='I agree to the' mod='eu_legal'}</label> <a href="{$PS_CONDITIONS_CMS_ID_LINK}" class="iframe">{l s='terms of service'  mod='eu_legal'}</a>
							{/if}
							{if isset($LEGAL_CMS_ID_REVOCATION) && $LEGAL_CMS_ID_REVOCATION}
							   <label for="cgv">{l s='and'  mod='eu_legal'}</label> <a href="{$LEGAL_CMS_ID_REVOCATION_LINK}" class="iframe">{l s='terms of revocation' mod='eu_legal'}</a> 
							{/if}
                            <label for="cgv">{l s='adhire to them unconditionally.'  mod='eu_legal'}</label>
						</p>
					</div>

					{if $is_partially_virtual}
					<div {if !$opc}style="display:none" data-show-if-js{/if}>
						<p class="carrier_title">{l s='Revocation' mod='eu_legal'}</p>
						<p class="checkbox">
							{if isset($PS_CONDITIONS_CMS_ID) && $PS_CONDITIONS_CMS_ID}
							<input type="checkbox" name="revocation_terms_aggreed" id="revocation_terms_aggreed" value="1"/>
							{/if}
							<label for="revocation_terms_aggreed">{l s='I agree that the digital products in my cart can not be returned or refunded due to the nature of such products.' mod='eu_legal'}</label>
						</p>
					</div>
					{/if}
					{include file="$legal_theme_dir/order-summary.tpl"}
				{/if}
				
					<p class="cart_navigation clearfix">
						{if !$opc}
                            <a href="{$link->getPageLink('order', true, NULL, "step=2")|escape:'html':'UTF-8'}" title="{l s='Previous' mod='eu_legal'}" class="button-exclusive btn btn-default">
                                <i class="icon-chevron-left"></i>
                                {l s='Continue shopping' mod='eu_legal'}
                            </a>
                        {/if}
					{include file="$legal_theme_dir/order-confirm.tpl"}
					</p>
				{if $opc}
					</div> <!-- end opc_payment_methods -->
				{/if}
			</div> <!-- end HOOK_TOP_PAYMENT -->
