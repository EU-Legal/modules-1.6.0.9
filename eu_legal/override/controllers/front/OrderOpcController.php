<?php
class OrderOpcController extends OrderOpcControllerCore {
    
    public function init()
    {
	    ParentOrderController::init();

	    if ($this->nbProducts)
		    $this->context->smarty->assign('virtual_cart', $this->context->cart->isVirtualCart());
	    
	    $this->context->smarty->assign('is_multi_address_delivery', $this->context->cart->isMultiAddressDelivery() || ((int)Tools::getValue('multi-shipping') == 1));
	    $this->context->smarty->assign('open_multishipping_fancybox', (int)Tools::getValue('multi-shipping') == 1);
	    
	    if ($this->context->cart->nbProducts())
	    {
		    if (Tools::isSubmit('ajax'))
		    {
			    if (Tools::isSubmit('method'))
			    {
				    switch (Tools::getValue('method'))
				    {
					    case 'getCartSummary':
					    if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-summary')) {
						$summary = $this->context->cart->getSummaryDetails();
						
						$this->context->smarty->assign($summary);
						
						$this->context->smarty->assign('HOOK_SHOPPING_CART', Hook::exec('displayShoppingCartFooter', $summary));
						die(Tools::jsonEncode(array(
						    'summary' => $this->context->smarty->fetch($tpl)
						)));
					    }
					    
					    break;
					    case 'updateMessage':
						    if (Tools::isSubmit('message'))
						    {
							    $txtMessage = urldecode(Tools::getValue('message'));
							    $this->_updateMessage($txtMessage);
							    if (count($this->errors))
								    die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
							    die(true);
						    }
						    break;

					    case 'updateCarrierAndGetPayments':
						    if ((Tools::isSubmit('delivery_option') || Tools::isSubmit('id_carrier')) && Tools::isSubmit('recyclable') && Tools::isSubmit('gift') && Tools::isSubmit('gift_message'))
						    {
							    $this->_assignWrappingAndTOS();
							    if ($this->_processCarrier())
							    {
								    $carriers = $this->context->cart->simulateCarriersOutput();
								    $return = array_merge(array(
									    'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
									    'HOOK_PAYMENT' => $this->_getPaymentMethods(),
									    'carrier_data' => $this->_getCarrierList(),
									    'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array('carriers' => $carriers))
									    ),
									    $this->getFormatedSummaryDetail()
								    );
								    Cart::addExtraCarriers($return);
								    die(Tools::jsonEncode($return));
							    }
							    else
								    $this->errors[] = Tools::displayError('An error occurred while updating the cart.');
							    if (count($this->errors))
								    die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
							    exit;
						    }
						    break;

					    case 'updateTOSStatusAndGetPayments':
						    if (Tools::isSubmit('checked'))
						    {
							    $this->context->cookie->checkedTOS = (int)(Tools::getValue('checked'));
							    die(Tools::jsonEncode(array(
								    'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
								    'HOOK_PAYMENT' => $this->_getPaymentMethods()
							    )));
						    }
						    break;

					    case 'getCarrierList':
						    die(Tools::jsonEncode($this->_getCarrierList()));
						    break;

					    case 'editCustomer':
						    if (!$this->isLogged)
							    exit;
						    if (Tools::getValue('years'))
							    $this->context->customer->birthday = (int)Tools::getValue('years').'-'.(int)Tools::getValue('months').'-'.(int)Tools::getValue('days');
						    $_POST['lastname'] = $_POST['customer_lastname'];
						    $_POST['firstname'] = $_POST['customer_firstname'];
						    $this->errors = $this->context->customer->validateController();
						    $this->context->customer->newsletter = (int)Tools::isSubmit('newsletter');
						    $this->context->customer->optin = (int)Tools::isSubmit('optin');
						    $return = array(
							    'hasError' => !empty($this->errors),
							    'errors' => $this->errors,
							    'id_customer' => (int)$this->context->customer->id,
							    'token' => Tools::getToken(false)
						    );
						    if (!count($this->errors))
							    $return['isSaved'] = (bool)$this->context->customer->update();
						    else
							    $return['isSaved'] = false;
						    die(Tools::jsonEncode($return));
						    break;

					    case 'getAddressBlockAndCarriersAndPayments':
						    if ($this->context->customer->isLogged())
						    {
							    // check if customer have addresses
							    if (!Customer::getAddressesTotalById($this->context->customer->id))
								    die(Tools::jsonEncode(array('no_address' => 1)));
							    if (file_exists(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php'))
							    {
								    include_once(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php');
								    $blockUserInfo = new BlockUserInfo();
							    }
							    $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
							    $this->_processAddressFormat();
							    $this->_assignAddress();

							    if (!($formatedAddressFieldsValuesList = $this->context->smarty->getTemplateVars('formatedAddressFieldsValuesList')))
								    $formatedAddressFieldsValuesList = array();

							    // Wrapping fees
							    $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
							    $wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
							    $return = array_merge(array(
								    'order_opc_adress' => $this->context->smarty->fetch(_PS_THEME_DIR_.'order-address.tpl'),
								    'block_user_info' => (isset($blockUserInfo) ? $blockUserInfo->hookDisplayTop(array()) : ''),
								    'formatedAddressFieldsValuesList' => $formatedAddressFieldsValuesList,
								    'carrier_data' => $this->_getCarrierList(),
								    'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
								    'HOOK_PAYMENT' => $this->_getPaymentMethods(),
								    'no_address' => 0,
								    'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)($this->context->cookie->id_currency))))
								    ),
								    $this->getFormatedSummaryDetail()
							    );
							    die(Tools::jsonEncode($return));
						    }
						    die(Tools::displayError());
						    break;

					    case 'makeFreeOrder':
						    /* Bypass payment step if total is 0 */
						    if (($id_order = $this->_checkFreeOrder()) && $id_order)
						    {
							    $order = new Order((int)$id_order);
							    $email = $this->context->customer->email;
							    if ($this->context->customer->is_guest)
								    $this->context->customer->logout(); // If guest we clear the cookie for security reason
							    die('freeorder:'.$order->reference.':'.$email);
						    }
						    exit;
						    break;

					    case 'updateAddressesSelected':
						    if ($this->context->customer->isLogged(true))
						    {
							    $address_delivery = new Address((int)(Tools::getValue('id_address_delivery')));
							    $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
							    $address_invoice = ((int)(Tools::getValue('id_address_delivery')) == (int)(Tools::getValue('id_address_invoice')) ? $address_delivery : new Address((int)(Tools::getValue('id_address_invoice'))));
							    if ($address_delivery->id_customer != $this->context->customer->id || $address_invoice->id_customer != $this->context->customer->id)
								    $this->errors[] = Tools::displayError('This address is not yours.');
							    elseif (!Address::isCountryActiveById((int)(Tools::getValue('id_address_delivery'))))
								    $this->errors[] = Tools::displayError('This address is not in a valid area.');
							    elseif (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted)
								    $this->errors[] = Tools::displayError('This address is invalid.');
							    else
							    {
								    $this->context->cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
								    $this->context->cart->id_address_invoice = Tools::isSubmit('same') ? $this->context->cart->id_address_delivery : (int)(Tools::getValue('id_address_invoice'));
								    if (!$this->context->cart->update())
									    $this->errors[] = Tools::displayError('An error occurred while updating your cart.');
									    
								    $infos = Address::getCountryAndState((int)($this->context->cart->id_address_delivery));
								    if (isset($infos['id_country']) && $infos['id_country'])
								    {
									    $country = new Country((int)$infos['id_country']);
									    $this->context->country = $country;
								    }

								    // Address has changed, so we check if the cart rules still apply
								    $cart_rules = $this->context->cart->getCartRules();
								    CartRule::autoRemoveFromCart($this->context);
								    CartRule::autoAddToCart($this->context);
								    if ((int)Tools::getValue('allow_refresh'))
								    {
									    // If the cart rules has changed, we need to refresh the whole cart
									    $cart_rules2 = $this->context->cart->getCartRules();
									    if (count($cart_rules2) != count($cart_rules))
										    $this->ajax_refresh = true;
									    else
									    {
										    $rule_list = array();
										    foreach ($cart_rules2 as $rule)
											    $rule_list[] = $rule['id_cart_rule'];
										    foreach ($cart_rules as $rule)
											    if (!in_array($rule['id_cart_rule'], $rule_list))
											    {
												    $this->ajax_refresh = true;
												    break;
											    }
									    }
								    }									
	    
								    if (!$this->context->cart->isMultiAddressDelivery())
									    $this->context->cart->setNoMultishipping(); // As the cart is no multishipping, set each delivery address lines with the main delivery address

								    if (!count($this->errors))
								    {
									    $result = $this->_getCarrierList();
									    // Wrapping fees
									    $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
									    $wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
									    $result = array_merge($result, array(
										    'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
										    'HOOK_PAYMENT' => $this->_getPaymentMethods(),
										    'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)($this->context->cookie->id_currency)))),
										    'carrier_data' => $this->_getCarrierList(),
										    'refresh' => (bool)$this->ajax_refresh),
										    $this->getFormatedSummaryDetail()
									    );
									    die(Tools::jsonEncode($result));
								    }
							    }
							    if (count($this->errors))
								    die(Tools::jsonEncode(array(
									    'hasError' => true,
									    'errors' => $this->errors
								    )));
						    }
						    die(Tools::displayError());
						    break;

					    case 'multishipping':
						    $this->_assignSummaryInformations();
						    $this->context->smarty->assign('product_list', $this->context->cart->getProducts());
						    
						    if ($this->context->customer->id)
							    $this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
						    else
							    $this->context->smarty->assign('address_list', array());
						    $this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping-products.tpl');
						    $this->display();
						    die();
						    break;

					    case 'cartReload':
						    $this->_assignSummaryInformations();
						    if ($this->context->customer->id)
							    $this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
						    else
							    $this->context->smarty->assign('address_list', array());
						    $this->context->smarty->assign('opc', true);
						    $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
						    $this->display();
						    die();
						    break;

					    case 'noMultiAddressDelivery':
						    $this->context->cart->setNoMultishipping();
						    die();
						    break;

					    default:
						    throw new PrestaShopException('Unknown method "'.Tools::getValue('method').'"');
				    }
			    }
			    else
				    throw new PrestaShopException('Method is not defined');
		    }
	    }
	    elseif (Tools::isSubmit('ajax'))
	    {
		    $this->errors[] = Tools::displayError('No product in your cart.');
		    die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
	    }
    }
    
    public function initContent() {
	parent::initContent();
	
	if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-opc')) {
	    $this->setTemplate($tpl);
	}
    }
    
    protected function _assignCarrier() {
	parent::_assignCarrier();
	
	if ( ! $this->isLogged) {
	    $this->context->smarty->assign('PS_EU_PAYMENT_API', Configuration::get('PS_EU_PAYMENT_API') ? true : false);
	}
	
    }
    
    protected function _getCarrierList()
    {
	    $address_delivery = new Address($this->context->cart->id_address_delivery);
	    
	    $cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
	    $link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, Configuration::get('PS_SSL_ENABLED'));
	    if (!strpos($link_conditions, '?'))
		    $link_conditions .= '?content_only=1';
	    else
		    $link_conditions .= '&content_only=1';
	    
	    $carriers = $this->context->cart->simulateCarriersOutput();
	    $delivery_option = $this->context->cart->getDeliveryOption(null, false, false);

	    $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
	    $wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
	    $oldMessage = Message::getMessageByCartId((int)($this->context->cart->id));
	    
	    $free_shipping = false;
	    foreach ($this->context->cart->getCartRules() as $rule)
	    {
		    if ($rule['free_shipping'] && !$rule['carrier_restriction'])
		    {
			    $free_shipping = true;
			    break;
		    }			
	    }
	    
	    $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());

	    $vars = array(
		    'free_shipping' => $free_shipping,
		    'checkedTOS' => (int)($this->context->cookie->checkedTOS),
		    'recyclablePackAllowed' => (int)(Configuration::get('PS_RECYCLABLE_PACK')),
		    'giftAllowed' => (int)(Configuration::get('PS_GIFT_WRAPPING')),
		    'cms_id' => (int)(Configuration::get('PS_CONDITIONS_CMS_ID')),
		    'conditions' => (int)(Configuration::get('PS_CONDITIONS')),
		    'link_conditions' => $link_conditions,
		    'recyclable' => (int)($this->context->cart->recyclable),
		    'gift_wrapping_price' => (float)$wrapping_fees,
		    'total_wrapping_cost' => Tools::convertPrice($wrapping_fees_tax_inc, $this->context->currency),
		    'total_wrapping_tax_exc_cost' => Tools::convertPrice($wrapping_fees, $this->context->currency),
		    'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
		    'carriers' => $carriers,
		    'checked' => $this->context->cart->simulateCarrierSelectedOutput(),
		    'delivery_option' => $delivery_option,
		    'address_collection' => $this->context->cart->getAddressCollection(),
		    'opc' => true,
		    'oldMessage' => isset($oldMessage['message'])? $oldMessage['message'] : '',
		    'PS_EU_PAYMENT_API' => Configuration::get('PS_EU_PAYMENT_API'),
		    'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
			    'carriers' => $carriers,
			    'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
			    'delivery_option' => $delivery_option
		    ))
	    );
	    
	    Cart::addExtraCarriers($vars);
	    
	    $this->context->smarty->assign($vars);

	    if (!Address::isCountryActiveById((int)($this->context->cart->id_address_delivery)) && $this->context->cart->id_address_delivery != 0)
		    $this->errors[] = Tools::displayError('This address is not in a valid area.');
	    elseif ((!Validate::isLoadedObject($address_delivery) || $address_delivery->deleted) && $this->context->cart->id_address_delivery != 0)
		    $this->errors[] = Tools::displayError('This address is invalid.');
	    else
	    {
		    $result = array(
			    'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
				    'carriers' => $carriers,
				    'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
				    'delivery_option' => $this->context->cart->getDeliveryOption(null, true)
			    ))
		    );
		    
		    if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-carrier')) {
			$result['carrier_block'] = $this->context->smarty->fetch($tpl);
		    }
		    else {
			$result['carrier_block'] = $this->context->smarty->fetch(_PS_THEME_DIR_.'order-carrier.tpl');
		    }

		    Cart::addExtraCarriers($result);
		    return $result;
	    }
	    if (count($this->errors)) {
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-carrier')) {
		    $carrier_tpl = $this->context->smarty->fetch($tpl);
		}
		else {
		    $carrier_tpl = $this->context->smarty->fetch(_PS_THEME_DIR_.'order-carrier.tpl');
		}
		
		return array(
		    'hasError' => true,
		    'errors' => $this->errors,
		    'carrier_block' => $carrier_tpl
		);
	    }
    }
    
    protected function _assignPayment() {
	if (Configuration::get('PS_EU_PAYMENT_API')) {
	    return ParentOrderController::_assignPayment();
	}
	else {
	    return parent::_assignPayment();
	}
    }
    
    protected function _getPaymentMethods() {
	    if (!$this->isLogged)
		    return '<p class="warning">'.Tools::displayError('Please sign in to see payment methods.').'</p>';
	    if ($this->context->cart->OrderExists())
		    return '<p class="warning">'.Tools::displayError('Error: This order has already been validated.').'</p>';
	    if (!$this->context->cart->id_customer || !Customer::customerIdExistsStatic($this->context->cart->id_customer) || Customer::isBanned($this->context->cart->id_customer))
		    return '<p class="warning">'.Tools::displayError('Error: No customer.').'</p>';
	    $address_delivery = new Address($this->context->cart->id_address_delivery);
	    $address_invoice = ($this->context->cart->id_address_delivery == $this->context->cart->id_address_invoice ? $address_delivery : new Address($this->context->cart->id_address_invoice));
	    if (!$this->context->cart->id_address_delivery || !$this->context->cart->id_address_invoice || !Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted)
		    return '<p class="warning">'.Tools::displayError('Error: Please select an address.').'</p>';
	    if (count($this->context->cart->getDeliveryOptionList()) == 0 && !$this->context->cart->isVirtualCart())
	    {
		    if ($this->context->cart->isMultiAddressDelivery())
			    return '<p class="warning">'.Tools::displayError('Error: None of your chosen carriers deliver to some of  the addresses you\'ve selected.').'</p>';
		    else
			    return '<p class="warning">'.Tools::displayError('Error: None of your chosen carriers deliver to the address you\'ve selected.').'</p>';
	    }
	    if (!$this->context->cart->getDeliveryOption(null, false) && !$this->context->cart->isVirtualCart())
		    return '<p class="warning">'.Tools::displayError('Error: Please choose a carrier.').'</p>';
	    if (!$this->context->cart->id_currency)
		    return '<p class="warning">'.Tools::displayError('Error: No currency has been selected.').'</p>';
	    if (!$this->context->cookie->checkedTOS && Configuration::get('PS_CONDITIONS') && !Configuration::get('PS_EU_PAYMENT_API'))
		    return '<p class="warning">'.Tools::displayError('Please accept the Terms of Service.').'</p>';
	    
	    /* If some products have disappear */
	    if (!$this->context->cart->checkQuantities())
		    return '<p class="warning">'.Tools::displayError('An item in your cart is no longer available. You cannot proceed with your order.').'</p>';

	    /* Check minimal amount */
	    $currency = Currency::getCurrency((int)$this->context->cart->id_currency);

	    $minimal_purchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
	    if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimal_purchase)
		    return '<p class="warning">'.sprintf(
			    Tools::displayError('A minimum purchase total of %1s (tax excl.) is required in order to validate your order, current purchase total is %2s (tax excl.).'),
			    Tools::displayPrice($minimal_purchase, $currency), Tools::displayPrice($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS), $currency)
		    ).'</p>';

	    /* Bypass payment step if total is 0 */
	    if ($this->context->cart->getOrderTotal() <= 0)
		    return '<p class="center"><button class="button btn btn-default button-medium" name="confirmOrder" id="confirmOrder" onclick="confirmFreeOrder();" type="submit"> <span>'.Tools::displayError('I confirm my order.').'</span></button></p>';

	    if (Configuration::get('PS_EU_PAYMENT_API'))
		    $return = $this->_getEuPaymentOptionsHTML();
	    else
		    $return = Hook::exec('displayPayment');
		    
	    if (!$return)
		    return '<p class="warning">'.Tools::displayError('No payment method is available for use at this time. ').'</p>';
	    return $return;
    }
}