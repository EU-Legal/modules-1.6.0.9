<?php

class OrderDetail extends OrderDetailCore
{
	
	public function saveTaxCalculator(Order $order, $replace = false)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* An der stelle darf nicht gerundet werden -> Rundungsproblem bei großen Stückzahlen
		*/
		
		// Nothing to save
		if ($this->tax_calculator == null)
			return true;

		if (!($this->tax_calculator instanceOf TaxCalculator))
			return false;

		if (count($this->tax_calculator->taxes) == 0)
			return true;

		if ($order->total_products <= 0)
			return true;

		$ratio = $this->unit_price_tax_excl / $order->total_products;
		$order_reduction_amount = $order->total_discounts_tax_excl * $ratio;
		$discounted_price_tax_excl = $this->unit_price_tax_excl - $order_reduction_amount;
		
		$values = '';
		foreach ($this->tax_calculator->getTaxesAmount($discounted_price_tax_excl) as $id_tax => $amount)
		{
			/* An der stelle darf nicht gerundet werden -> Rundungsproblem bei großen Stückzahlen */
			//$unit_amount = (float)Tools::ps_round($amount, 2);
			$unit_amount = (float)$amount;
			$total_amount = $unit_amount * $this->product_quantity;
			$values .= '('.(int)$this->id.','.(float)$id_tax.','.$unit_amount.','.(float)$total_amount.'),';
		}

		if ($replace)
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_detail_tax` WHERE id_order_detail='.(int)$this->id);
			
		$values = rtrim($values, ',');
		$sql = 'INSERT INTO `'._DB_PREFIX_.'order_detail_tax` (id_order_detail, id_tax, unit_amount, total_amount)
				VALUES '.$values;
		
		return Db::getInstance()->execute($sql);
		
	}
	
}
