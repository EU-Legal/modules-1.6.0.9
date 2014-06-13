<div id="orderSummaryWrapper">
	{hook h="displayBeforeShoppingCartBlock"}
	
	<div id="order-detail-content" class="table_block table-responsive">
		<table id="cart_summary" class="table table-bordered">
			<thead>
				<tr>
					<th class="cart_product first_item">{l s='Product' mod='eu_legal'}</th>
					<th class="cart_description item">{l s='Description' mod='eu_legal'}</th>
					{if $PS_STOCK_MANAGEMENT}
						<th class="cart_availability item">{l s='Avail.' mod='eu_legal'}</th>
					{/if}
					<th class="cart_unit item">{l s='Unit price' mod='eu_legal'}</th>
					<th class="cart_quantity item">{l s='Qty' mod='eu_legal'}</th>
					<th class="cart_total last_item">{l s='Total' mod='eu_legal'}</th>

				</tr>
			</thead>
			<tfoot>
				{if $use_taxes}
					{if $priceDisplay}
						<tr class="cart_total_price">
							<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total products (tax excl.)' mod='eu_legal'}{else}{l s='Total products' mod='eu_legal'}{/if}</td>
							<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
						</tr>
					{else}
						<tr class="cart_total_price">
							<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total products (tax incl.)' mod='eu_legal'}{else}{l s='Total products' mod='eu_legal'}{/if}</td>
							<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products_wt}</td>
						</tr>
					{/if}
				{else}
					<tr class="cart_total_price">
						<td colspan="4" class="text-right">{l s='Total products' mod='eu_legal'}</td>
						<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
					</tr>
				{/if}
				<tr class="cart_total_voucher" {if $total_wrapping == 0}style="display:none"{/if}>
					<td colspan="4" class="text-right">
						{if $use_taxes}
							{if $priceDisplay}
								{if $display_tax_label}{l s='Total gift wrapping (tax excl.):' mod='eu_legal'}{else}{l s='Total gift wrapping cost:' mod='eu_legal'}{/if}
							{else}
								{if $display_tax_label}{l s='Total gift wrapping (tax incl.)' mod='eu_legal'}{else}{l s='Total gift wrapping cost:' mod='eu_legal'}{/if}
							{/if}
						{else}
							{l s='Total gift wrapping cost:' mod='eu_legal'}
						{/if}
					</td>
					<td colspan="2" class="price-discount price" id="total_wrapping">
						{if $use_taxes}
							{if $priceDisplay}
								{displayPrice price=$total_wrapping_tax_exc}
							{else}
								{displayPrice price=$total_wrapping}
							{/if}
						{else}
							{displayPrice price=$total_wrapping_tax_exc}
						{/if}
					</td>
				</tr>
				{if ! $PS_EU_PAYMENT_API}
					{if $total_shipping_tax_exc <= 0 && !isset($virtualCart)}
						<tr class="cart_total_delivery">
							<td colspan="4" class="text-right">{l s='Shipping:' mod='eu_legal'}</td>
							<td colspan="2" class="price" id="total_shipping">{l s='Free Shipping!' mod='eu_legal'}</td>
						</tr>
					{else}
						{if $use_taxes && $total_shipping_tax_exc != $total_shipping}
							{if $priceDisplay}
								<tr class="cart_total_delivery" {if $shippingCost <= 0} style="display:none"{/if}>
									<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total shipping (tax excl.)' mod='eu_legal'}{else}{l s='Total shipping' mod='eu_legal'}{/if}</td>
									<td colspan="2" class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
								</tr>
							{else}
								<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none"{/if}>
									<td colspan="4" class="text-right">{if $display_tax_label}{l s='Total shipping (tax incl.)' mod='eu_legal'}{else}{l s='Total shipping' mod='eu_legal'}{/if}</td>
									<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
								</tr>
							{/if}
						{else}
							<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none"{/if}>
								<td colspan="4" class="text-right">{l s='Total shipping' mod='eu_legal'}</td>
								<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
							</tr>
						{/if}
					{/if}
				{else}
					<tr class="cart_total_delivery">
						<td colspan="4" class="text-right">{l s='Shipping' mod='eu_legal'}</td>
						<td colspan="2" class="price" id="total_shipping">{hook h="displayShippingPrice"}</td>
					</tr>
				{/if}
				<tr class="cart_total_voucher" {if $total_discounts == 0}style="display:none"{/if}>
					<td colspan="4" class="text-right">
						{if $use_taxes}
							{if $priceDisplay}
								{if $display_tax_label}{l s='Total vouchers (tax excl.)' mod='eu_legal'}{else}{l s='Total vouchers' mod='eu_legal'}{/if}
							{else}
								{if $display_tax_label}{l s='Total vouchers (tax incl.)' mod='eu_legal'}{else}{l s='Total vouchers' mod='eu_legal'}{/if}
							{/if}
						{else}
							{l s='Total vouchers' mod='eu_legal'}
						{/if}
					</td>
					<td colspan="2" class="price-discount price" id="total_discount">
						{if $use_taxes}
							{if $priceDisplay}
								{displayPrice price=$total_discounts_tax_exc*-1}
							{else}
								{displayPrice price=$total_discounts*-1}
							{/if}
						{else}
							{displayPrice price=$total_discounts_tax_exc*-1}
						{/if}
					</td>
				</tr>
				{if $use_taxes}
					{if $priceDisplay && $total_tax != 0}
						<tr class="cart_total_tax">
							<td colspan="4" class="text-right">{l s='Total tax:' mod='eu_legal'}</td>
							<td colspan="2" class="price" id="total_tax" >{displayPrice price=$total_tax}</td>
						</tr>
					{/if}
					<tr class="cart_total_price">
						<td colspan="4" class="total_price_container text-right"><span>{l s='Total' mod='eu_legal'}</span></td>
						<td colspan="2" class="price" id="total_price_container">
							<span id="total_price">{displayPrice price=$total_price}</span>
						</td>
					</tr>
				{else}
				<tr class="cart_total_price">
					{if $voucherAllowed}
						<td colspan="2" id="cart_voucher" class="cart_voucher">
							<div id="cart_voucher" class="table_block">
								{if isset($errors_discount) && $errors_discount}
									<ul class="alert alert-danger">
									{foreach from=$errors_discount key=k item=error}
										<li>{$error|escape:'html':'UTF-8'}</li>
									{/foreach}
									</ul>
								{/if}
								{if $voucherAllowed}
									<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
										<fieldset>
											<h4>{l s='Vouchers' mod='eu_legal'}</h4>
											<input type="text" id="discount_name" class="form-control" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
											<input type="hidden" name="submitDiscount" />
											<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='ok' mod='eu_legal'}</span></button>
											{if $displayVouchers}
												<p id="title" class="title_offers">{l s='Take advantage of our offers:' mod='eu_legal'}</p>
												<div id="display_cart_vouchers">
												{foreach from=$displayVouchers item=voucher}
													<span onclick="$('#discount_name').val('{$voucher.name}');return false;" class="voucher_name">{$voucher.name}</span> - {$voucher.description} <br />
												{/foreach}
												</div>
											{/if}
										</fieldset>
									</form>
								{/if}
							</div>
						</td>
					{/if}
					<td colspan="{if !$voucherAllowed}3{else}2{/if}" class="text-right total_price_container">
						<span>{l s='Total' mod='eu_legal'}</span>
					</td>
					<td colspan="1" class="price total_price_container" id="total_price_container">
						<span id="total_price">{displayPrice price=$total_price_without_tax}</span>
					</td>
				</tr>
				{/if}
			</tfoot>
	
			<tbody>
				{foreach from=$products item=product name=productLoop}
					{assign var='productId' value=$product.id_product}
					{assign var='productAttributeId' value=$product.id_product_attribute}
					{assign var='quantityDisplayed' value=0}
					{assign var='cannotModify' value=1}
					{assign var='odd' value=$product@iteration%2}
					{assign var='noDeleteButton' value=1}
	
					{* Display the product line *}
					{include file="$tpl_dir./shopping-cart-product-line.tpl"}
	
					{* Then the customized datas ones*}
					{if isset($customizedDatas.$productId.$productAttributeId)}
						{foreach from=$customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] key='id_customization' item='customization'}
							<tr id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" class="alternate_item cart_item">
								<td colspan="4">
									{foreach from=$customization.datas key='type' item='datas'}
										{if $type == $CUSTOMIZE_FILE}
											<div class="customizationUploaded">
												<ul class="customizationUploaded">
													{foreach from=$datas item='picture'}
														<li>
															<img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" />
														</li>
													{/foreach}
												</ul>
											</div>
										{elseif $type == $CUSTOMIZE_TEXTFIELD}
											<ul class="typedText">
												{foreach from=$datas item='textField' name='typedText'}
													<li>
														{if $textField.name}
															{l s='%s:' mod='eu_legal' sprintf=$textField.name}
														{else}
															{l s='Text #%s:' mod='eu_legal' sprintf=$smarty.foreach.typedText.index+1}
														{/if}
														{$textField.value}
													</li>
												{/foreach}
											</ul>
										{/if}
									{/foreach}
								</td>
								<td class="cart_quantity text-center">
									{$customization.quantity}
								</td>
								<td class="cart_total"></td>
							</tr>
							{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
						{/foreach}
						{* If it exists also some uncustomized products *}
						{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl"}{/if}
					{/if}
				{/foreach}
				{assign var='last_was_odd' value=$product@iteration%2}
				{foreach $gift_products as $product}
					{assign var='productId' value=$product.id_product}
					{assign var='productAttributeId' value=$product.id_product_attribute}
					{assign var='quantityDisplayed' value=0}
					{assign var='odd' value=($product@iteration+$last_was_odd)%2}
					{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
					{assign var='cannotModify' value=1}
					{* Display the gift product line *}
					{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
				{/foreach}
			</tbody>
	
			{if count($discounts)}
				<tbody>
					{foreach from=$discounts item=discount name=discountLoop}
						<tr class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
							<td class="cart_discount_name" colspan="{if $PS_STOCK_MANAGEMENT}3{else}2{/if}">{$discount.name}</td>
								<td class="cart_discount_price">
									<span class="price-discount">
										{if $discount.value_real > 0}
											{if !$priceDisplay}
												{displayPrice price=$discount.value_real*-1}
											{else}
												{displayPrice price=$discount.value_tax_exc*-1}
											{/if}
										{/if}
									</span>
								</td>
								<td class="cart_discount_delete">1</td>
								<td class="cart_discount_price">
								<span class="price-discount">
									{if $discount.value_real > 0}
										{if !$priceDisplay}
											{displayPrice price=$discount.value_real*-1}
										{else}
											{displayPrice price=$discount.value_tax_exc*-1}
										{/if}
									{/if}
								</span>
							</td>
						</tr>
					{/foreach}
				</tbody>
			{/if}
		</table>
	</div> <!-- end order-detail-content -->
	<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>
</div>