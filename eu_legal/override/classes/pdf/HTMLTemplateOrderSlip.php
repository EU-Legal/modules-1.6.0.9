<?php
class HTMLTemplateOrderSlip extends HTMLTemplateOrderSlipCore
{
	public function getTaxTabContent() {
		$invoice_address = new Address((int)$this->order->id_address_invoice);
		$tax_exempt = Configuration::get('VATNUMBER_MANAGEMENT') && !empty($invoice_address->vat_number) && $invoice_address->id_country != Configuration::get('VATNUMBER_COUNTRY');

		$this->smarty->assign(array(
			'tax_exempt'    => $tax_exempt,
			'tax_details'   => $this->order->getOrderTaxDetails(false, $this->order_slip),
			'order'         => $this->order,
			'is_order_slip' => true
		));

		return $this->smarty->fetch($this->getTemplate('invoice.tax-tab'));
	}
}
