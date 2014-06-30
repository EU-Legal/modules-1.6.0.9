<?php
class OrderController extends OrderControllerCore
{
    /*
     * Mostly copies parent's method, with the exception of PS_EU_PAYMENT_API condition in step 3
     * and tried to instersect the following templates:
     * order-carrier.tpl, order-payment.tpl and shopping-cart.tpl - method tries to replace them
     * with eu_legal tmeplates
     *
     * @access public
     *
     * @return void
     */
    public function initContent()
    {
	ParentOrderController::initContent();

	if (Tools::isSubmit('ajax') && Tools::getValue('method') == 'updateExtraCarrier') {
	    // Change virtualy the currents delivery options
	    $delivery_option = $this->context->cart->getDeliveryOption();
	    $delivery_option[(int)Tools::getValue('id_address')] = Tools::getValue('id_delivery_option');
	    $this->context->cart->setDeliveryOption($delivery_option);
	    $this->context->cart->save();
	    $return = array(
		'content' => Hook::exec(
		    'displayCarrierList',
		    array(
			'address' => new Address((int)Tools::getValue('id_address'))
		    )
		)
	    );
	    die(Tools::jsonEncode($return));
	}

	if ($this->nbProducts) {
	    $this->context->smarty->assign('virtual_cart', $this->context->cart->isVirtualCart());
	}

	// 4 steps to the order
	switch ((int)$this->step)
	{
	    case -1;
		$this->context->smarty->assign('empty', 1);
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('shopping-cart')) {
		    $this->setTemplate($tpl);
		}
		else {
		    $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
		}
	    break;

	    case 1:
		$this->_assignAddress();
		$this->processAddressFormat();
		if (Tools::getValue('multi-shipping') == 1)
		{
		    $this->_assignSummaryInformations();
		    $this->context->smarty->assign('product_list', $this->context->cart->getProducts());
		    $this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping.tpl');
		}
		else
		    $this->setTemplate(_PS_THEME_DIR_.'order-address.tpl');
	    break;

	    case 2:
		if (Tools::isSubmit('processAddress'))
			$this->processAddress();
		$this->autoStep();
		$this->_assignCarrier();
		
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-carrier')) {
		    $this->setTemplate($tpl);
		}
		else {
		    $this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
		}
	    break;

	    case 3:
		// Check that the conditions (so active) were accepted by the customer
		// Only do it when EU Payment Module API is not active: When EU Payment is active TOS are checked
		// on last page of order process... well, when Javascript is available :)
		if (!Configuration::get('PS_EU_PAYMENT_API') || Tools::getValue('cgv_checkbox_shown'))
		{
		    $cgv = Tools::getValue('cgv') || $this->context->cookie->check_cgv;
		    if (Configuration::get('PS_CONDITIONS') && (!Validate::isBool($cgv) || $cgv == false))
			Tools::redirect('index.php?controller=order&step=2');
		    Context::getContext()->cookie->check_cgv = true;
		}

		// Check the delivery option is set
		if (!$this->context->cart->isVirtualCart())
		{
		    if (!Tools::getValue('delivery_option') && !Tools::getValue('id_carrier') && !$this->context->cart->delivery_option && !$this->context->cart->id_carrier)
			    Tools::redirect('index.php?controller=order&step=2');
		    elseif (!Tools::getValue('id_carrier') && !$this->context->cart->id_carrier)
		    {
			$deliveries_options = Tools::getValue('delivery_option');
			if (!$deliveries_options)
			    $deliveries_options = $this->context->cart->delivery_option;

			foreach ($deliveries_options as $delivery_option)
			    if (empty($delivery_option))
				Tools::redirect('index.php?controller=order&step=2');
		    }
		}

		$this->autoStep();

		// Bypass payment step if total is 0
		if (($id_order = $this->_checkFreeOrder()) && $id_order)
		{
		    if ($this->context->customer->is_guest)
		    {
			$order = new Order((int)$id_order);
			$email = $this->context->customer->email;
			$this->context->customer->mylogout(); // If guest we clear the cookie for security reason
			Tools::redirect('index.php?controller=guest-tracking&id_order='.urlencode($order->reference).'&email='.urlencode($email));
		    }
		    else
			Tools::redirect('index.php?controller=history');
		}
		$this->_assignPayment();
		// assign some informations to display cart
		$this->_assignSummaryInformations();
		
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-payment')) {
		    $this->setTemplate($tpl);
		}
		else {
		    $this->setTemplate(_PS_THEME_DIR_.'order-payment.tpl');
		}
		
		break;

	    default:
		$this->_assignSummaryInformations();
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('shopping-cart')) {
		    $this->setTemplate($tpl);
		}
		else {
		    $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
		}
		break;
	}

	$this->context->smarty->assign(array(
	    'currencySign' => $this->context->currency->sign,
	    'currencyRate' => $this->context->currency->conversion_rate,
	    'currencyFormat' => $this->context->currency->format,
	    'currencyBlank' => $this->context->currency->blank,
	));
    }
    
}
