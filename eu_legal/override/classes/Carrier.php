<?php
/**
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
 */

class Carrier extends CarrierCore
{
	public function getTaxesRate(Address $address)
	{
		
		/*
		* EU-Legal
		* alternative method for tax calculation instead of static taxrate (LEGAL_SHIPTAXMETH)
		* Only if Cart exists and at least one product in cart, otherwise the alternative method could not be applied
		*/
		if(
			Configuration::get('LEGAL_SHIPTAXMETH') and 
			$cart = Context::getContext()->cart and 
			$products = $cart->getProducts() and 
			!empty($products)
		)
			return Cart::getTaxesAverageUsed($cart->id);
		
		return parent::getTaxesRate($address);
		
	}
	
}