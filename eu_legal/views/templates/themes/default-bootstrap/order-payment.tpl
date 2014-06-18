{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !$opc}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
	{capture name=path}{l s='Your payment method'}{/capture}
	<h1 class="page-heading">{l s='Please choose your payment method'}</h1>
{else}
	<h1 class="page-heading step-num"><span>3</span> {l s='Please choose your payment method'}</h1>
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
					<p class="alert alert-warning">{l s='No payment modules have been installed.'}</p>
				{/if}
				
				
				{if isset($PS_EU_PAYMENT_API) and $PS_EU_PAYMENT_API}
					<div {if !$opc}style="display:none" data-show-if-js{/if}>
						<p class="carrier_title">{l s='Terms of service'}</p>
						<p class="checkbox">
							{if isset($PS_CONDITIONS_CMS_ID) && $PS_CONDITIONS_CMS_ID}
							<input type="checkbox" name="cgv" id="cgv" value="1"/>
							{/if}
							{if isset($PS_CONDITIONS_CMS_ID) && $PS_CONDITIONS_CMS_ID}
							   <label for="cgv">{l s='I agree to the' mod='eu_legal'}</label> <a href="{$PS_CONDITIONS_CMS_ID_LINK}" class="iframe">{l s='terms of service' mod='germanext'}</a>
							{/if}
							{if isset($LEGAL_CMS_ID_REVOCATION) && $LEGAL_CMS_ID_REVOCATION}
							   <label>{l s='and' mod='germanext'}</label> <a href="{$LEGAL_CMS_ID_REVOCATION_LINK}" class="iframe">{l s='terms of revocation' mod='germanext'}</a> {l s='adhire to them unconditionally.' mod='germanext'}
							{/if}
						</p>
					</div>
					{if $is_partially_virtual}
					<div {if !$opc}style="display:none" data-show-if-js{/if}>
						<p class="carrier_title">{l s='Revocation'}</p>
						<p class="checkbox">
							{if isset($PS_CONDITIONS_CMS_ID) && $PS_CONDITIONS_CMS_ID}
							<input type="checkbox" name="revocation_terms_aggreed" id="revocation_terms_aggreed" value="1"/>
							{/if}
							<label for="cgv">{l s='I agree that the digital products in my cart can not be returned or refunded due to the nature of such products.' mod='eu_legal'}</label>
						</p>
					</div>
					{/if}
					{include file="$legal_theme_dir/order-summary.tpl"}
					{include file="$legal_theme_dir/order-confirm.tpl"}
				{/if}
				
				{if !$opc}
					<p class="cart_navigation clearfix">
						<a href="{$link->getPageLink('order', true, NULL, "step=2")|escape:'html':'UTF-8'}" title="{l s='Previous'}" class="button-exclusive btn btn-default">
							<i class="icon-chevron-left"></i>
							{l s='Continue shopping'}
						</a>
					</p>
				{else}
					</div> <!-- end opc_payment_methods -->
				{/if}
			</div> <!-- end HOOK_TOP_PAYMENT -->