<?php

class Carrier extends CarrierCore
{
	
	
	
	
	
	public function getTaxesRate(Address $address)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Alternative Methode zur Berechnung der MwSt. anstatt statischer taxrate (LEGAL_SHIPTAXMETH)
		*/
		
		/*
		* Alternative Methode zur Berechnung der MwSt. anstatt statischer taxrate (LEGAL_SHIPTAXMETH)
		* Nur wenn Warenkorb existiert und mindestens ein Produkt im Warenkorb, sonst kann alternative methode nicht angewand werden
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

