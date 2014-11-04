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
{if $opc}
	{assign var="back_order_page" value="order-opc.php"}
	{else}
	{assign var="back_order_page" value="order.php"}
{/if}
{capture name=path}{l s='Your shopping cart' mod='eu_legal'}{/capture}
<h1 id="cart_title" class="page-heading">{l s='Your shopping cart' mod='eu_legal'}</h1>
{if $PS_CATALOG_MODE}
	<p class="alert alert-warning">{l s='Your new order was not accepted.' mod='eu_legal'}</p>
{else}
	{if $productNumber}
		<!-- Shopping Cart -->
		{* eu-legal: hide if EU API enabled *}
		{if !isset($PS_EU_PAYMENT_API) or !$PS_EU_PAYMENT_API}
		{include file="$tpl_dir./shopping-cart.tpl"}
		{else}
			{*set js init vars if EU PAYMENT IS ACTIVE,(needed for guest checkout) *}
			{strip}
			{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
			{addJsDef currencyRate=$currencyRate|floatval}
			{addJsDef currencyFormat=$currencyFormat|intval}
			{addJsDef currencyBlank=$currencyBlank|intval}
			{addJsDef deliveryAddress=$cart->id_address_delivery|intval}
			{addJsDefL name=txtProduct}{l s='product' mod='eu_legal' js=1}{/addJsDefL}
			{addJsDefL name=txtProducts}{l s='products' mod='eu_legal' js=1}{/addJsDefL}
			{/strip}
		{/if}
		<!-- End Shopping Cart -->
		{if $is_logged AND !$is_guest}
			{include file="$tpl_dir./order-address.tpl"}
		{else}
			<!-- Create account / Guest account / Login block -->
			{include file="$tpl_dir./order-opc-new-account.tpl"}
			<!-- END Create account / Guest account / Login block -->
		{/if}
		<!-- Carrier -->
		{include file="$legal_theme_dir./order-carrier.tpl"}
		<!-- END Carrier -->
	
		<!-- Payment -->
		{include file="$legal_theme_dir./order-payment.tpl"}
		<!-- END Payment -->
	{else}
		{capture name=path}{l s='Your shopping cart' mod='eu_legal'}{/capture}
		<h2 class="page-heading">{l s='Your shopping cart' mod='eu_legal'}</h2>
		{include file="$tpl_dir./errors.tpl"}
		<p class="alert alert-warning">{l s='Your shopping cart is empty.' mod='eu_legal'}</p>
	{/if}
{strip}
{addJsDef imgDir=$img_dir}
{addJsDef authenticationUrl=$link->getPageLink("authentication", true)|addslashes}
{addJsDef orderOpcUrl=$link->getPageLink("order-opc", true)|addslashes}
{addJsDef historyUrl=$link->getPageLink("history", true)|addslashes}
{addJsDef guestTrackingUrl=$link->getPageLink("guest-tracking", true)|addslashes}
{addJsDef addressUrl=$link->getPageLink("address", true, NULL, "back={$back_order_page}")|addslashes}
{addJsDef orderProcess='order-opc'}
{addJsDef guestCheckoutEnabled=$PS_GUEST_CHECKOUT_ENABLED|intval}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef displayPrice=$priceDisplay}
{addJsDef taxEnabled=$use_taxes}
{addJsDef conditionEnabled=$conditions|intval}
{addJsDef vat_management=$vat_management|intval}
{addJsDef errorCarrier=$errorCarrier|@addcslashes:'\''}
{addJsDef errorTOS=$errorTOS|@addcslashes:'\''}
{addJsDef checkedCarrier=$checked|intval}
{addJsDef addresses=array()}
{addJsDef isVirtualCart=$isVirtualCart|intval}
{addJsDef isPaymentStep=$isPaymentStep|intval}
{addJsDefL name=txtWithTax}{l s='(tax incl.)' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtWithoutTax}{l s='(tax excl.)' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtHasBeenSelected}{l s='has been selected' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtNoCarrierIsSelected}{l s='No carrier has been selected' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtNoCarrierIsNeeded}{l s='No carrier is needed for this order' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtConditionsIsNotNeeded}{l s='You do not need to accept the Terms of Service for this order.' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtTOSIsAccepted}{l s='The service terms have been accepted' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtTOSIsNotAccepted}{l s='The service terms have not been accepted' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtThereis}{l s='There is' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtErrors}{l s='Error(s)' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtDeliveryAddress}{l s='Delivery address' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtInvoiceAddress}{l s='Invoice address' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtModifyMyAddress}{l s='Modify my address' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtInstantCheckout}{l s='Instant checkout' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtSelectAnAddressFirst}{l s='Please start by selecting an address.' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtFree}{l s='Free' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtProduct}{l s='product' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtProducts}{l s='products' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtNoPaymentMethodIsSelected}{l s='No payment method has been selected' mod='eu_legal' js=1}{/addJsDefL}
{addJsDefL name=txtRevocationTermIsNotAccepted}{l s='The revocation terms have not been accepted' mod='eu_legal' js=1}{/addJsDefL}

{capture}{if $back}&mod={$back|urlencode}{/if}{/capture}
{capture name=addressUrl}{$link->getPageLink('address', true, NULL, 'back='|cat:$back_order_page|cat:'?step=1'|cat:$smarty.capture.default)|addslashes}{/capture}
{addJsDef addressUrl=$smarty.capture.addressUrl}
{capture}{'&multi-shipping=1'|urlencode}{/capture}
{addJsDef addressMultishippingUrl=$smarty.capture.addressUrl|cat:$smarty.capture.default}
{capture name=addressUrlAdd}{$smarty.capture.addressUrl|cat:'&id_address='}{/capture}
{addJsDef addressUrlAdd=$smarty.capture.addressUrlAdd}
{addJsDef opc=$opc|boolval}
{capture}<h3 class="page-subheading">{l s='Your billing address' mod='eu_legal' js=1}</h3>{/capture}
{addJsDefL name=titleInvoice}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<h3 class="page-subheading">{l s='Your delivery address' mod='eu_legal' js=1}</h3>{/capture}
{addJsDefL name=titleDelivery}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<a class="button button-small btn btn-default" href="{$smarty.capture.addressUrlAdd}" title="{l s='Update' mod='eu_legal' js=1}"><span>{l s='Update' mod='eu_legal' js=1}<i class="icon-chevron-right right"></i></span></a>{/capture}
{addJsDefL name=liUpdate}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{/strip}
{/if}
