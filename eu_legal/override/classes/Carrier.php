<?php
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