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
{if !$opc}
	{capture name=path}{l s='Shipping:' mod='eu_legal'}{/capture}
	{assign var='current_step' value='shipping'}
	<div id="carrier_area">
		<h1 class="page-heading">{l s='Shipping:' mod='eu_legal'}</h1>
		{include file="$tpl_dir./order-steps.tpl"}
		{include file="$tpl_dir./errors.tpl"}
		<form id="form" action="{$link->getPageLink('order', true, NULL, "multi-shipping={$multi_shipping}")|escape:'html':'UTF-8'}" method="post" name="carrier_area">
{else}
	<div id="carrier_area" class="opc-main-block">
		<h1 class="page-heading step-num"><span>2</span> {l s='Delivery methods' mod='eu_legal'}</h1>
			<div id="opc_delivery_methods" class="opc-main-block">
				<div id="opc_delivery_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}
<div class="order_carrier_content box">
	{if isset($virtual_cart) && $virtual_cart}
		<input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
	{else}
		<div id="HOOK_BEFORECARRIER">
			{if isset($carriers) && isset($HOOK_BEFORECARRIER)}
				{$HOOK_BEFORECARRIER}
			{/if}
		</div>
		{if isset($isVirtualCart) && $isVirtualCart}
			<p class="alert alert-warning">{l s='No carrier is needed for this order.' mod='eu_legal'}</p>
		{else}
			{if $recyclablePackAllowed}
				<div class="checkbox">
					<label for="recyclable">
						<input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} />
						{l s='I would like to receive my order in recycled packaging.' mod='eu_legal'}.
					</label>
				</div>
			{/if}
			<div class="delivery_options_address">
				{if isset($delivery_option_list)}
					{foreach $delivery_option_list as $id_address => $option_list}
						<p class="carrier_title">
							{if isset($address_collection[$id_address])}
								{l s='Choose a shipping option for this address:' mod='eu_legal'} {$address_collection[$id_address]->alias}
							{else}
								{l s='Choose a shipping option' mod='eu_legal'}
							{/if}
						</p>
						<div class="delivery_options">
							{foreach $option_list as $key => $option}
								<div class="delivery_option {if ($option@index % 2)}alternate_{/if}item">
									<div>
										<table class="resume table table-bordered{if !$option.unique_carrier} not-displayable{/if}">
											<tr>
												<td class="delivery_option_radio">
													<input id="delivery_option_{$id_address|intval}_{$option@index}" class="delivery_option_radio" type="radio" name="delivery_option[{$id_address|intval}]" data-key="{$key}" data-id_address="{$id_address|intval}" value="{$key}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} />
												</td>
												<td class="delivery_option_logo">
													{foreach $option.carrier_list as $carrier}
														{if $carrier.logo}
															<img src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}"/>
														{else if !$option.unique_carrier}
															{$carrier.instance->name|escape:'htmlall':'UTF-8'}
															{if !$carrier@last} - {/if}
														{/if}
													{/foreach}
												</td>
												<td>
													{if $option.unique_carrier}
														{foreach $option.carrier_list as $carrier}
															<strong>{$carrier.instance->name|escape:'htmlall':'UTF-8'}</strong>
														{/foreach}
														{if isset($carrier.instance->delay[$cookie->id_lang])}
															{$carrier.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
														{/if}
													{/if}
													{if count($option_list) > 1}
														{if $option.is_best_grade}
															{if $option.is_best_price}
																{l s='The best price and speed' mod='eu_legal'}
															{else}
																{l s='The fastest' mod='eu_legal'}
															{/if}
														{else}
															{if $option.is_best_price}
																{l s='The best price' mod='eu_legal'}
															{/if}
														{/if}
													{/if}
												</td>
												<td class="delivery_option_price">
													<div class="delivery_option_price">
														{if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
															{if $use_taxes == 1}
																{if $priceDisplay == 1}
																	{convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='eu_legal'}{/if}
																{else}
																	{convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='eu_legal'}{/if}
																{/if}
															{else}
																{convertPrice price=$option.total_price_without_tax}
															{/if}
														{else}
															{l s='Free' mod='eu_legal'}
														{/if}
													</div>
												</td>
											</tr>
										</table>
										{if !$option.unique_carrier}
											<table class="delivery_option_carrier{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} selected{/if} resume table table-bordered{if $option.unique_carrier} not-displayable{/if}">
												<tr>
													{if !$option.unique_carrier}
														<td rowspan="{$option.carrier_list|@count}" class="delivery_option_radio first_item">
															<input id="delivery_option_{$id_address|intval}_{$option@index}" class="delivery_option_radio" type="radio" name="delivery_option[{$id_address|intval}]" data-key="{$key}" data-id_address="{$id_address|intval}" value="{$key}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} />
														</td>
													{/if}
													{assign var="first" value=current($option.carrier_list)}
													<td class="delivery_option_logo{if $first.product_list[0].carrier_list[0] eq 0} not-displayable{/if}">
														{if $first.logo}
															<img src="{$first.logo|escape:'htmlall':'UTF-8'}" alt="{$first.instance->name|escape:'htmlall':'UTF-8'}"/>
														{else if !$option.unique_carrier}
															{$first.instance->name|escape:'htmlall':'UTF-8'}
														{/if}
													</td>
													<td class="{if $option.unique_carrier}first_item{/if}{if $first.product_list[0].carrier_list[0] eq 0} not-displayable{/if}">
														<input type="hidden" value="{$first.instance->id|intval}" name="id_carrier" />
														{if isset($first.instance->delay[$cookie->id_lang])}
															<i class="icon-info-sign"></i>{$first.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
															{if count($first.product_list) <= 1}
																({l s='Product concerned:' mod='eu_legal'}
															{else}
																({l s='Products concerned:' mod='eu_legal'}
															{/if}
															{foreach $first.product_list as $product}
																{if $product@index == 4}
																	<acronym title="
																{/if}
																{strip}
																	{if $product@index >= 4}
																		{$product.name|escape:'htmlall':'UTF-8'}
																		{if isset($product.attributes) && $product.attributes}
																			{$product.attributes|escape:'htmlall':'UTF-8'}
																		{/if}
																		{if !$product@last}
																			,&nbsp;
																		{else}
																			">&hellip;</acronym>)
																		{/if}
																	{else}
																		{$product.name|escape:'htmlall':'UTF-8'}
																		{if isset($product.attributes) && $product.attributes}
																			{$product.attributes|escape:'htmlall':'UTF-8'}
																		{/if}
																		{if !$product@last}
																			,&nbsp;
																		{else}
																			)
																		{/if}
																	{/if}
																{strip}
															{/foreach}
														{/if}
													</td>
													<td rowspan="{$option.carrier_list|@count}" class="delivery_option_price">
														<div class="delivery_option_price">
															{if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
																{if $use_taxes == 1}
																	{if $priceDisplay == 1}
																		{convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='eu_legal'}{/if}
																	{else}
																		{convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='eu_legal'}{/if}
																	{/if}
																{else}
																	{convertPrice price=$option.total_price_without_tax}
																{/if}
															{else}
																{l s='Free' mod='eu_legal'}
															{/if}
														</div>
													</td>
												</tr>
												<tr>
													<td class="delivery_option_logo{if $carrier.product_list[0].carrier_list[0] eq 0} not-displayable{/if}">
														{foreach $option.carrier_list as $carrier}
															{if $carrier@iteration != 1}
																{if $carrier.logo}
																	<img src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}"/>
																{else if !$option.unique_carrier}
																	{$carrier.instance->name|escape:'htmlall':'UTF-8'}
																{/if}
															{/if}
														{/foreach}
													</td>
													<td class="{if $option.unique_carrier} first_item{/if}{if $carrier.product_list[0].carrier_list[0] eq 0} not-displayable{/if}">
														<input type="hidden" value="{$first.instance->id|intval}" name="id_carrier" />
														{if isset($carrier.instance->delay[$cookie->id_lang])}
															<i class="icon-info-sign"></i>
															{$first.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
															{if count($carrier.product_list) <= 1}
																({l s='Product concerned:' mod='eu_legal'}
															{else}
																({l s='Products concerned:' mod='eu_legal'}
															{/if}
															{foreach $carrier.product_list as $product}
																{if $product@index == 4}
																	<acronym title="
																{/if}
																{strip}
																	{if $product@index >= 4}
																		{$product.name|escape:'htmlall':'UTF-8'}
																		{if isset($product.attributes) && $product.attributes}
																			{$product.attributes|escape:'htmlall':'UTF-8'}
																		{/if}
																		{if !$product@last}
																			,&nbsp;
																		{else}
																			">&hellip;</acronym>)
																		{/if}
																	{else}
																		{$product.name|escape:'htmlall':'UTF-8'}
																		{if isset($product.attributes) && $product.attributes}
																			{$product.attributes|escape:'htmlall':'UTF-8'}
																		{/if}
																		{if !$product@last}
																			,&nbsp;
																		{else}
																			)
																		{/if}
																	{/if}
																{strip}
															{/foreach}
														{/if}
													</td>
												</tr>
											</table>
										{/if}
									</div>
								</div> <!-- end delivery_option -->
							{/foreach}
						</div> <!-- end delivery_options -->
						<div class="hook_extracarrier" id="HOOK_EXTRACARRIER_{$id_address|escape:'htmlall'}">
							{if isset($HOOK_EXTRACARRIER_ADDR) &&  isset($HOOK_EXTRACARRIER_ADDR.$id_address)}{$HOOK_EXTRACARRIER_ADDR.$id_address}{/if}
						</div>
						{foreachelse}
							<p class="alert alert-warning" id="noCarrierWarning">
								{foreach $cart->getDeliveryAddressesWithoutCarriers(true) as $address}
									{if empty($address->alias)}
										{l s='No carriers available.' mod='eu_legal'}
									{else}
										{l s='No carriers available for the address "%s".' mod='eu_legal' sprintf=$address->alias}
									{/if}
									{if !$address@last}
										<br />
									{/if}
								{foreachelse}
									{l s='No carriers available.' mod='eu_legal'}
								{/foreach}
							</p>
						{/foreach}
					{/if}
				</div> <!-- end delivery_options_address -->
				{if $opc}
					<p class="carrier_title">{l s='Leave a message' mod='eu_legal'}</p>
					<div>
						<p>{l s='If you would like to add a comment about your order, please write it in the field below.' mod='eu_legal'}</p>
						<textarea class="form-control" cols="120" rows="2" name="message" id="message">{strip}
							{if isset($oldMessage)}{$oldMessage|escape:'html':'UTF-8'}{/if}
						{/strip}</textarea>
					</div>
					<hr style="" />
				{/if}
				<div id="extra_carrier" style="display: none;"></div>
					{if $giftAllowed}
						<p class="carrier_title">{l s='Gift' mod='eu_legal'}</p>
						<p class="checkbox gift">
							<input type="checkbox" name="gift" id="gift" value="1" {if $cart->gift == 1}checked="checked"{/if} />
							<label for="gift">
								{l s='I would like my order to be gift wrapped.' mod='eu_legal'}
								{if $gift_wrapping_price > 0}
									&nbsp;<i>({l s='Additional cost of' mod='eu_legal'}
									<span class="price" id="gift-price">
										{if $priceDisplay == 1}
											{convertPrice price=$total_wrapping_tax_exc_cost}
										{else}
											{convertPrice price=$total_wrapping_cost}
										{/if}
									</span>
									{if $use_taxes && $display_tax_label}
										{if $priceDisplay == 1}
											{l s='(tax excl.)' mod='eu_legal'}
										{else}
											{l s='(tax incl.)' mod='eu_legal'}
										{/if}
									{/if})
									</i>
								{/if}
							</label>
						</p>
						<p id="gift_div">
							<label for="gift_message">{l s='If you\'d like, you can add a note to the gift:' mod='eu_legal'}</label>
							<textarea rows="2" cols="120" id="gift_message" class="form-control" name="gift_message">{$cart->gift_message|escape:'html':'UTF-8'}</textarea>
						</p>
						{if $opc}
							<hr style="" />
						{/if}
					{/if}
				{/if}
			{/if}
			{if (!isset($PS_EU_PAYMENT_API) || !$PS_EU_PAYMENT_API) && $conditions AND $cms_id}
				<p class="carrier_title">{l s='Terms of service' mod='eu_legal'}</p>
				<p class="checkbox">
					<input type="checkbox" name="cgv" id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
					<label for="cgv">{l s='I agree to the terms of service and will adhere to them unconditionally.' mod='eu_legal'}</label>
					<a href="{$link_conditions|escape:'html':'UTF-8'}" class="iframe" rel="nofollow">{l s='(Read the Terms of Service)' mod='eu_legal'}</a>
				</p>
			{/if}
		</div> <!-- end delivery_options_address -->
		{if !$opc}
				<p class="cart_navigation clearfix">
					<input type="hidden" name="step" value="3" />
					<input type="hidden" name="back" value="{$back|escape:'htmlall'}" />
					{if !$is_guest}
						{if $back}
							<a href="{$link->getPageLink('order', true, NULL, "step=1&back={$back}&multi-shipping={$multi_shipping}")|escape:'html':'UTF-8'}" title="{l s='Previous' mod='eu_legal'}" class="button-exclusive btn btn-default">
								<i class="icon-chevron-left"></i>
								{l s='Continue shopping' mod='eu_legal'}
							</a>
						{else}
							<a href="{$link->getPageLink('order', true, NULL, "step=1&multi-shipping={$multi_shipping}")|escape:'html':'UTF-8'}" title="{l s='Previous' mod='eu_legal'}" class="button-exclusive btn btn-default">
								<i class="icon-chevron-left"></i>
								{l s='Continue shopping' mod='eu_legal'}
							</a>
						{/if}
					{else}
						<a href="{$link->getPageLink('order', true, NULL, "multi-shipping={$multi_shipping}")|escape:'html':'UTF-8'}" title="{l s='Previous' mod='eu_legal'}" class="button-exclusive btn btn-default">
							<i class="icon-chevron-left"></i>
							{l s='Continue shopping' mod='eu_legal'}
						</a>
					{/if}
					{if isset($virtual_cart) && $virtual_cart || (isset($delivery_option_list) && !empty($delivery_option_list))}
						<button type="submit" name="processCarrier" class="button btn btn-default standard-checkout button-medium">
							<span>
								{l s='Proceed to checkout' mod='eu_legal'}
								<i class="icon-chevron-right right"></i>
							</span>
						</button>
					{/if}
				</p>
			</form>
	{else}
		</div> <!-- end opc_delivery_methods -->
	{/if}
</div> <!-- end carrier_area -->
{strip}
{if !$opc}
	{addJsDef orderProcess='order'}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{if isset($virtual_cart) && !$virtual_cart && $giftAllowed && $cart->gift == 1}
		{addJsDef cart_gift=true}
	{else}
		{addJsDef cart_gift=false}
	{/if}
	{addJsDef orderUrl=$link->getPageLink("order", true)|escape:'quotes':'UTF-8'}
	{addJsDefL name=txtProduct}{l s='Product' mod='eu_legal' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='Products' mod='eu_legal' js=1}{/addJsDefL}
{/if}
{if $conditions}
	{addJsDefL name=msg_order_carrier}{l s='You must agree to the terms of service before continuing.' mod='eu_legal' js=1}{/addJsDefL}
{/if}
{/strip}