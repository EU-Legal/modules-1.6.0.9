<?php
class HTMLTemplateInvoice extends HTMLTemplateInvoiceCore
{
	public function getTaxTabContent()
	{
		$debug = Tools::getValue('debug');

		$address = new Address((int)$this->order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
		$tax_exempt = Configuration::get('VATNUMBER_MANAGEMENT')
							&& !empty($address->vat_number)
							&& $address->id_country != Configuration::get('VATNUMBER_COUNTRY');
		$carrier = new Carrier($this->order->id_carrier);
			
		$data = array(
			'tax_exempt' => $tax_exempt,
			'use_one_after_another_method' => $this->order_invoice->useOneAfterAnotherTaxComputationMethod(),
			'product_tax_breakdown' => $this->order_invoice->getProductTaxesBreakdown(),
			'shipping_tax_breakdown' => $this->order_invoice->getShippingTaxesBreakdown($this->order),
			'ecotax_tax_breakdown' => $this->order_invoice->getEcoTaxTaxesBreakdown(),
			'wrapping_tax_breakdown' => $this->order_invoice->getWrappingTaxesBreakdown(),
			'order' => $debug ? null : $this->order,
			'order_invoice' => $debug ? null : $this->order_invoice,
			'carrier' => $debug ? null : $carrier
		);
		
		if (method_exists($this->order, 'getOrderTaxDetails')) {
			$data['tax_details'] = $this->order->getOrderTaxDetails();
			$data['is_order_slip'] = false;
		}

		if ($debug)
			return $data;

		$this->smarty->assign($data);

		return $this->smarty->fetch($this->getTemplate('invoice.tax-tab'));
	}
}

