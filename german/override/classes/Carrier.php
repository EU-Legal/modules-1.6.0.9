<?php

class Carrier extends CarrierCore
{
	
	public function getTaxesRate(Address $address)
	{
		
		/*
		* GC German 1.5.6.0 | 20131022
		* Alternative Methode zur Berechnung der MwSt. anhand Produkte im Warenkorb (GC_GERMAN_SHIPTAXMETH)
		*/
		
		/* 
		* Alternative Methode zur Berechnung der MwSt. anhand Produkte im Warenkorb (GC_GERMAN_SHIPTAXMETH)
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

