<?php

class Carrier extends CarrierCore
{
	
	public function getTaxesRate(Address $address)
	{
		
		/*
		* GC German 1.5.6.0 | 20131022
		* Alternative Methode zur Berechnung der MwSt. anstatt statischer taxrate (GC_GERMAN_SHIPTAXMETH)
		*/
		
		/*
		* Alternative Methode zur Berechnung der MwSt. anstatt statischer taxrate (GC_GERMAN_SHIPTAXMETH)
		* Nur wenn Warenkorb existiert und mindestens ein Produkt im Warenkorb, sonst kann alternative methode nicht angewand werden
		*/
		if(
			Configuration::get('GC_GERMAN_SHIPTAXMETH') and 
			$cart = Context::getContext()->cart and 
			$products = $cart->getProducts() and 
			!empty($products)
		)
			return Cart::getTaxesAverageUsed($cart->id);
		
		return parent::getTaxesRate($address);
		
	}
	
}

