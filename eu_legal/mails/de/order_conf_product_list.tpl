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
{foreach $list as $product}
<tr>
	<td style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td>
					<font size="2" face="Open-sans, sans-serif" color="#555454">
						{$product['reference']|escape:'htmlall'}
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
	<td style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td>
					<font size="2" face="Open-sans, sans-serif" color="#555454">
						<strong>{$product['name']|escape:'htmlall'}</strong>
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
	<td style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td align="right">
					<font size="2" face="Open-sans, sans-serif" color="#555454">
						{$product['unit_price']|escape:'htmlall'}
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
	<td style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td align="right">
					<font size="2" face="Open-sans, sans-serif" color="#555454">
						{$product['quantity']|escape:'htmlall'}
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
	<td style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td align="right">
					<font size="2" face="Open-sans, sans-serif" color="#555454">
						{$product['price']|escape:'htmlall'}
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
	{foreach $product['customization'] as $customization}
		<tr>
		<td colspan="2" style="border:1px solid #D6D4D4;">
			<table class="table">
				<tr>
					<td width="10">&nbsp;</td>
					<td>
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							<strong>{$product['name']|escape:'htmlall'}</strong><br>
							{$customization['customization_text']|escape:'htmlall'}
						</font>
					</td>
					<td width="10">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid #D6D4D4;">
			<table class="table">
				<tr>
					<td width="10">&nbsp;</td>
					<td align="right">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							{$product['unit_price']|escape:'htmlall'}
						</font>
					</td>
					<td width="10">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid #D6D4D4;">
			<table class="table">
				<tr>
					<td width="10">&nbsp;</td>
					<td align="right">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							{$customization['customization_quantity']|escape:'htmlall'}
						</font>
					</td>
					<td width="10">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid #D6D4D4;">
			<table class="table">
				<tr>
					<td width="10">&nbsp;</td>
					<td align="right">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							{$customization['quantity']|escape:'htmlall'}
						</font>
					</td>
					<td width="10">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	{/foreach}
{/foreach}