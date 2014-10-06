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
{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<div id="container-customer">
	<div class="row">
		{*left*}
		<div class="col-lg-6">
			<div class="panel clearfix">
				<div class="panel-heading">
					<i class="icon-user"></i>
					{$customer->firstname|escape:'htmlall'}
					{$customer->lastname|escape:'htmlall'}
					[{$customer->id|string_format:"%06d"}]
					-
					<a href="mailto:{$customer->email|escape:'htmlall'}"><i class="icon-envelope"></i>
						{$customer->email}
					</a>
					<div class="panel-heading-action">
						<a class="btn btn-default" href="{$current|escape:'htmlall'}&amp;updatecustomer&amp;id_customer={$customer->id|escape:'htmlall'}&amp;token={$token|escape:'htmlall'}">
							<i class="icon-edit"></i>
							{l s='Edit'}
						</a>
					</div>
				</div>
				<div class="form-horizontal">
					<div class="row">
						<label class="control-label col-lg-3">{l s='Social Title'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">{if $gender->name}{$gender->name}{else}{l s='Unknown'}{/if}</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Age'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">
								{if isset($customer->birthday) && $customer->birthday != '0000-00-00'}
									{l s='%1$d years old (birth date: %2$s)' sprintf=[$customer_stats['age'], $customer_birthday]}
								{else}
									{l s='Unknown'}
								{/if}
							</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Registration Date'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">{$registration_date|escape:'htmlall'}</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Last Visit'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">{if $customer_stats['last_visit']}{$last_visit}{else}{l s='Never'}{/if}</p>
						</div>
					</div>
					{if $count_better_customers != '-'}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Best Customer Rank'}</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$count_better_customers|escape:'htmlall'}</p>
							</div>
						</div>
					{/if}
					{if $shop_is_feature_active}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Shop'}</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$name_shop|escape:'htmlall'}</p>
							</div>
						</div>
					{/if}
					<div class="row">
						<label class="control-label col-lg-3">{l s='Language'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">
								{if isset($customerLanguage)}
									{$customerLanguage->name|escape:'htmlall'}
								{else}
									{l s='Unknown'}
								{/if}
							</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Registrations'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">
								{if $customer->newsletter}
									<span class="label label-success">
										<i class="icon-check"></i>
										{l s='Newsletter'}
									</span>
								{else}
									<span class="label label-danger">
										<i class="icon-remove"></i>
										{l s='Newsletter'}
									</span>
								{/if}
								&nbsp;
								{if $customer->optin}
									<span class="label label-success">
										<i class="icon-check"></i>
										{l s='Opt in'}
									</span>
									{else}
									<span class="label label-danger">
										<i class="icon-remove"></i>
										{l s='Opt in'}
									</span>
								{/if}
							</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Latest Update'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">{$last_update|escape:'htmlall'}</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3">{l s='Status'}</label>
						<div class="col-lg-9">
							<p class="form-control-static">
								{if $customer->active}
									<span class="label label-success">
										<i class="icon-check"></i>
										{l s='Active'}
									</span>
								{else}
									<span class="label label-danger">
										<i class="icon-remove"></i>
										{l s='Inactive'}
									</span>
								{/if}
							</p>
						</div>
					</div>
				</div>
				{if $customer->isGuest()}
					{l s='This customer is registered as.'} <b>{l s='Guest'}</b>
					{if !$customer_exists}
					<form method="post" action="index.php?tab=AdminCustomers&amp;id_customer={$customer->id}&amp;token={getAdminToken tab='AdminCustomers'}">
						<input type="hidden" name="id_lang" value="{$id_lang|escape:'htmlall'}" />
						<p class="text-center">
							<input class="button" type="submit" name="submitGuestToCustomer" value="{l s='Transform to a customer account'}" />
						</p>
						{l s='This feature generates a random password before sending an email to your customer.'}
					</form>
					{else}
					<p class="text-muted text-center">
						{l s='A registered customer account using the defined email address already exists. '}
					</p>
					{/if}
				{/if}
			</div>
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-file"></i> {l s='Orders'} <span class="badge">{count($orders)|escape:'htmlall'}</span>
				</div>
				{if $orders AND count($orders)}
					{assign var=count_ok value=count($orders_ok)}
					{assign var=count_ko value=count($orders_ko)}
					<div class="panel">
						<div class="row">
							<div class="col-lg-6">
								<i class="icon-ok-circle icon-big"></i>
								{l s='Valid orders:'}
								<span class="label label-success">{$count_ok|escape:'htmlall'}</span>
								{l s='for'} {$total_ok}
							</div>
							<div class="col-lg-6">
								<i class="icon-exclamation-sign icon-big"></i>
								{l s='Invalid orders:'}
								<span class="label label-danger">{$count_ko|escape:'htmlall'}</span>
							</div>
						</div>
					</div>
					
					{if $count_ok}
						<table class="table">
							<thead>
								<tr>
									<th class="center"><span class="title_box ">{l s='ID'}</span></th>
									<th><span class="title_box">{l s='Date'}</span></th>
									<th><span class="title_box">{l s='Payment'}</span></th>
									<th><span class="title_box">{l s='Status'}</span></th>
									<th><span class="title_box">{l s='Products'}</span></th>
									<th><span class="title_box ">{l s='Total spent'}</span></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							{foreach $orders_ok AS $key => $order}
								<tr onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
									<td>{$order['id_order']|escape:'htmlall'}</td>
									<td>{dateFormat date=$order['date_add'] full=0}</td>
									<td>{$order['payment']|escape:'htmlall'}</td>
									<td>{$order['order_state']|escape:'htmlall'}</td>
									<td>{$order['nb_products']|escape:'htmlall'}</td>
									<td>{$order['total_paid_real']|escape:'htmlall'}</td>
									<td>
										<a class="btn btn-default" href="?tab=AdminOrders&amp;id_order={$order['id_order']}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">
											<i class='icon-search'></i> {l s='View'}
										</a>
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					{/if}

					{if $count_ko}
						<table class="table">
							<thead>
								<tr>
									<th><span class="title_box ">{l s='ID'}</span></th>
									<th><span class="title_box ">{l s='Date'}</span></th>
									<th><span class="title_box ">{l s='Payment'}</span></th>
									<th><span class="title_box ">{l s='Status'}</span></th>
									<th><span class="title_box ">{l s='Products'}</span></th>
									<th><span class="title_box ">{l s='Total spent'}</span></th>
								</tr>
							</thead>
							<tbody>
								{foreach $orders_ko AS $key => $order}
								<tr onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
									<td>{$order['id_order']|escape:'htmlall'}</td>
									<td><a href="?tab=AdminOrders&amp;id_order={$order['id_order']}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">{dateFormat date=$order['date_add'] full=0}</a></td>
									<td>{$order['payment']|escape:'htmlall'}</td>
									<td>{$order['order_state']|escape:'htmlall'}</td>
									<td>{$order['nb_products']|escape:'htmlall'}</td>
									<td>{$order['total_paid_real']|escape:'htmlall'}</td>
								</tr>
								{/foreach}
							</tbody>
						</table>	
					{/if}
				{else}
				<p class="text-muted text-center">
					{l s='%1$s %2$s has not placed any orders yet' sprintf=[$customer->firstname, $customer->lastname]}
				</p>
				{/if}
			</div>

			<div class="panel">
				<div class="panel-heading">
					<i class="icon-shopping-cart"></i> {l s='Carts'} <span class="badge">{count($carts)|escape:'htmlall'}</span>
				</div>
				{if $carts AND count($carts)}
					<table class="table">
						<thead>
							<tr>
								<th><span class="title_box ">{l s='ID'}</span></th>
								<th><span class="title_box ">{l s='Date'}</a></span></th>
								<th><span class="title_box ">{l s='Carrier'}</span></th>
								<th><span class="title_box ">{l s='Total'}</span></th>
							</tr>
						</thead>
						<tbody>
						{foreach $carts AS $key => $cart}
							<tr onclick="document.location = '?tab=AdminCarts&id_cart={$cart['id_cart']}&viewcart&token={getAdminToken tab='AdminCarts'}'">
								<td>{$cart['id_cart']|escape:'htmlall'}</td>
								<td>
									<a href="index.php?tab=AdminCarts&amp;id_cart={$cart['id_cart']}&amp;viewcart&amp;token={getAdminToken tab='AdminCarts'}">
										{dateFormat date=$cart['date_upd'] full=0}
									</a>
								</td>
								<td>{$cart['name']|escape:'htmlall'}</td>
								<td>{$cart['total_price']|escape:'htmlall'}</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				{else}
				<p class="text-muted text-center">
					{l s='No cart is available'}
				</p>
				{/if}
			</div>
			{if $products AND count($products)}
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-archive"></i> {l s='Purchased products'} <span class="badge">{count($products)|escape:'htmlall'}</span>
				</div>
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box">{l s='Date'}</span></th>
							<th><span class="title_box">{l s='Name'}</span></th>
							<th><span class="title_box">{l s='Quantity'}</span></th>
						</tr>
					</thead>
					<tbody>
						{foreach $products AS $key => $product}
						<tr onclick="document.location = '?tab=AdminOrders&id_order={$product['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
							<td>{dateFormat date=$order['date_add'] full=0}</td>
							<td>
								<a href="?tab=AdminOrders&amp;id_order={$product['id_order']}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">
									{$product['product_name']|escape:'htmlall'}
								</a>
							</td>
							<td>{$product['product_quantity']|escape:'htmlall'}</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{/if}
			{if count($interested)}
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-archive"></i> {l s='Purchased products'} <span class="badge">{count($interested)|escape:'htmlall'}</span>
				</div>
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box ">{l s='ID'}</span></th>
							<th><span class="title_box ">{l s='Name'}</span></th>
						</tr>
					</thead>
					<tbody>
					{foreach $interested as $key => $p}
						<tr onclick="document.location = '{$p['url']|escape:'htmlall'}'">
							<td>{$p['id']|escape:'htmlall'}</td>
							<td><a href="{$p['url']|escape:'htmlall'}">{$p['name']|escape:'htmlall'}</a></td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			{/if}
		</div>
		{*right*}
		<div class="col-lg-6">
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-eye-close"></i> {l s='Add a private note'}
				</div>
				<div class="alert alert-info">{l s='This note will be displayed to all employees but not to customers.'}</div>
				<form id="customer_note" class="form-horizontal" action="ajax.php" method="post" onsubmit="saveCustomerNote({$customer->id|escape:'htmlall'});return false;" >
					<div class="form-group">
						<div class="col-lg-12">
							<textarea name="note" id="noteContent" onkeyup="$(this).val().length > 0 ? $('#submitCustomerNote').removeAttr('disabled') : $('#submitCustomerNote').attr('disabled', 'disabled')">{$customer_note|escape:'htmlall'}</textarea>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<button type="submit" id="submitCustomerNote" class="btn btn-default pull-right" disabled="disabled" />
								<i class="icon-save"></i>
								{l s='Save'}
							</button>
						</div>
					</div>
					<span id="note_feedback"></span>
				</form>
			</div>
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-envelope"></i> {l s='Messages'} <span class="badge">{count($messages)|escape:'htmlall'}</span>
				</div>
				{if count($messages)}
					<table class="table">
						<thead>
							<th><span class="title_box">{l s='Status'}</span></th>
							<th><span class="title_box">{l s='Message'}</span></th>
							<th><span class="title_box">{l s='Sent on'}</span></th>
						</thead>
						{foreach $messages AS $message}
							<tr>
								<td>{$message['status']|escape:'htmlall'}</td>
								<td>
									<a href="index.php?tab=AdminCustomerThreads&amp;id_customer_thread={$message.id_customer_thread}&amp;viewcustomer_thread&amp;token={getAdminToken tab='AdminCustomerThreads'}">
										{$message['message']|escape:'htmlall'}...
									</a>
								</td>
								<td>{$message['date_add']|escape:'htmlall'}</td>
							</tr>
						{/foreach}
					</table>
				{else}
				<p class="text-muted text-center">
					{l s='%1$s %2$s has never contacted you' sprintf=[$customer->firstname, $customer->lastname]}
				</p>
				{/if}
			</div>
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-ticket"></i> {l s='Vouchers'} <span class="badge">{count($discounts)|escape:'htmlall'}</span>
				</div>
				{if count($discounts)}
					<table class="table">
						<thead>
							<tr>
								<th><span class="title_box ">{l s='ID'}</span></th>
								<th><span class="title_box ">{l s='Code'}</span></th>
								<th><span class="title_box ">{l s='Name'}</span></th>
								<th><span class="title_box ">{l s='Status'}</span></th>
							<tr/>
						</thead>
						<tbody>
					{foreach $discounts AS $key => $discount}
							<tr>
								<td>{$discount['id_cart_rule']|escape:'htmlall'}</td>
								<td>{$discount['code']|escape:'htmlall'}</td>
								<td>{$discount['name']|escape:'htmlall'}</td>
								<td>
									{if $discount['active']}
										<i class="icon-ok"></i>
									{else}
										<i class="icon-remove"></i>
									{/if}
								</td>
								<td>
									<a href="?tab=AdminCartRules&amp;id_cart_rule={$discount['id_cart_rule']}&amp;addcart_rule&amp;token={getAdminToken tab='AdminCartRules'}">
										<i class="icon-pencil"></i>
									</a>
									<a href="?tab=AdminCartRules&amp;id_cart_rule={$discount['id_cart_rule']}&amp;deletecart_rule&amp;token={getAdminToken tab='AdminCartRules'}">
										<i class="icon-remove"></i>
									</a>
								</td>
							</tr>
						</tbody>
					{/foreach}
					</table>
				{else}
				<p class="text-muted text-center">
					{l s='%1$s %2$s has no discount vouchers' sprintf=[$customer->firstname, $customer->lastname]}
				</p>
				{/if}
			</div>

			{if count($connections)}
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-time"></i> {l s='Last connections'}
				</div>
				<table class="table">
					<thead>
					<tr>
						<th><span class="title_box">{l s='Date'}</span></th>
						<th><span class="title_box">{l s='Pages viewed'}</span></th>
						<th><span class="title_box">{l s='Total time'}</span></th>
						<th><span class="title_box">{l s='Origin'}</span></th>
						{*<th><span class="title_box">{l s='IP Address'}</span></th>*}
					</tr>
					</thead>
					<tbody>
					{foreach $connections as $connection}
						<tr>
							<td>{dateFormat date=$connection['date_add'] full=0}</td>
							<td>{$connection['pages']|escape:'htmlall'}</td>
							<td>{$connection['time']|escape:'htmlall'}</td>
							<td>{$connection['http_referer']|escape:'htmlall'}</td>
							{*<td>{$connection['ipaddress']}</td>*}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
			{/if}

			<div class="panel">
				<div class="panel-heading">
					<i class="icon-group"></i>
					{l s='Groups'}
					<span class="badge">{count($groups)|escape:'htmlall'}</span>
					<a class="btn btn-default pull-right" href="{$current|escape:'htmlall'}&amp;updatecustomer&amp;id_customer={$customer->id|escape:'htmlall'}&amp;token={$token|escape:'htmlall'}">
						<i class="icon-edit"></i> {l s='Edit'}
					</a>
				</div>
				{if $groups AND count($groups)}
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box ">{l s='ID'}</span></th>
							<th><span class="title_box ">{l s='Name'}</span></th>
						</tr>
					</thead>
					<tbody>
						{foreach $groups AS $key => $group}
						<tr onclick="document.location = '?tab=AdminGroups&amp;id_group={$group['id_group']}&amp;viewgroup&amp;token={getAdminToken tab='AdminGroups'}'">
							<td>{$group['id_group']|escape:'htmlall'}</td>
							<td>
								<a href="?tab=AdminGroups&amp;id_group={$group['id_group']}&amp;viewgroup&amp;token={getAdminToken tab='AdminGroups'}">
									{$group['name']|escape:'htmlall'}
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
				{/if}
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-6">
			
		</div>
		<div class="col-lg-6">
			{if count($referrers)}
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-share-alt"></i> {l s='Referrers'}
				</div>
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box ">{l s='Date'}</span></th>
							<th><span class="title_box ">{l s='Name'}</span></th>
							{if $shop_is_feature_active}<th>{l s='Shop'}</th>{/if}
						</tr>
					</thead>
					<tbody>
						{foreach $referrers as $referrer}
						<tr>
							<td>{dateFormat date=$order['date_add'] full=0}</td>
							<td>{$referrer['name']|escape:'htmlall'}</td>
							{if $shop_is_feature_active}<td>{$referrer['shop_name']}</td>{/if}
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{/if}
		</div>
	</div>

	<div class="row">
		{* display hook specified to this page : AdminCustomers *}
		{hook h="displayAdminCustomers" id_customer=$customer->id}
		<div class="col-lg-12">
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-map-marker"></i> {l s='Addresses'} <span class="badge">{count($addresses)|escape:'htmlall'}</span>
				</div>
				{if count($addresses)}
					<table class="table">
						<thead>
							<tr>
								<th><span class="title_box ">{l s='Company'}</span></th>
								<th><span class="title_box ">{l s='Name'}</span></th>
								<th><span class="title_box ">{l s='Address'}</span></th>
								<th><span class="title_box ">{l s='Country'}</span></th>
								<th><span class="title_box ">{l s='Phone number(s)'}</span></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							{foreach $addresses AS $key => $address}
							<tr>
								<td>{if $address['company']}{$address['company']}{else}--{/if}</td>
								<td>{$address['firstname']|escape:'htmlall'} {$address['lastname']|escape:'htmlall'}</td>
								<td>{$address['address1']|escape:'htmlall'} {if $address['address2']}{$address['address2']|escape:'htmlall'}{/if} {$address['postcode']|escape:'htmlall'} {$address['city']|escape:'htmlall'}</td>
								<td>{$address['country']|escape:'htmlall'}</td>
								<td>
									{if $address['phone']}
										{$address['phone']|escape:'htmlall'}
										{if $address['phone_mobile']}<br />{$address['phone_mobile']|escape:'htmlall'}{/if}
									{else}
										{if $address['phone_mobile']}<br />{$address['phone_mobile']|escape:'htmlall'}{else}--{/if}
									{/if}
								</td>
								<td class="text-right">
									<div class="btn-group">
										<a class="btn btn-default" href="?tab=AdminAddresses&amp;id_address={$address['id_address']}&amp;addaddress=1&amp;token={getAdminToken tab='AdminAddresses'}">
											<i class="icon-edit"></i> {l s='Edit'}
										</a>
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a href="?tab=AdminAddresses&amp;id_address={$address['id_address']}&amp;deleteaddress&amp;token={getAdminToken tab='AdminAddresses'}">
													<i class="icon-trash"></i>
													{l s='Delete'}
												</a>
											</li>
										</ul>
									</div>
								</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				{else}
					<p class="text-muted text-center">
						{l s='%1$s %2$s has not registered any addresses yet' sprintf=[$customer->firstname, $customer->lastname]}
					</p>
				{/if}
			</div>
		</div>
	</div>
</div>
{/block}
