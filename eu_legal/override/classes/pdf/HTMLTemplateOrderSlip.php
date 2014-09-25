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

class HTMLTemplateOrderSlip extends HTMLTemplateOrderSlipCore
{
	public function getTaxTabContent()
	{
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