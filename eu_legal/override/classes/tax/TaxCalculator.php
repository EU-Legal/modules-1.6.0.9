<?php
class TaxCalculator extends TaxCalculatorCore
{
	public function getTaxData() {
		$prepared = array();
		$id_lang = (int)Context::getContext()->language->id;

		foreach ($this->taxes as $tax) {
			$prepared[$tax->id] = array(
				'name' => $tax->name[$id_lang],
				'rate' => $tax->rate
			);
		}
		
		return $prepared;
	}
}
