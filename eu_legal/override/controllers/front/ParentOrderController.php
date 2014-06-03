<?php
class ParentOrderController extends ParentOrderControllerCore {
    protected $_legal = false;
    
    public function __construct() {
	parent::__construct();
	
	$instance = Module::getInstanceByName('eu_legal');

	if (Validate::isLoadedObject($instance) && Module::isInstalled($instance->name) && Module::isEnabled($instance->name)) {
	    $this->_legal = $instance;
	}
    }
    
    private function getLegalInstance() {
	return $this->_legal;
    }
    
    public function init() {
	parent::init();
	
	$cms = array(
	    'PS_CONDITIONS_CMS_ID', 'LEGAL_CMS_ID_REVOCATION'
	);
	
	foreach ($cms as $config) {
	    if ($id_cms = Configuration::get($config)) {
		$cms = new CMS((int)$id_cms, $this->context->language->id);
		
		if (Validate::isLoadedObject($cms)) {
		    $this->context->smarty->assign($config, new CMS((int)$id_cms, $this->context->language->id));
		    
		    $link = $this->context->link->getCMSLink($cms);

		    if ( ! strpos($link, '?')) {
			$link.= '?content_only=1';
		    }
		    else {
			$link.= '&content_only=1';
		    }
		    
		    $this->context->smarty->assign($config . '_LINK', $link);
		}
	    }
	}
	
	$this->context->smarty->assign(array(
	    'is_partially_virtual' => $this->context->cart->containsVirtualProducts(),
	    'PS_EU_PAYMENT_API' => Configuration::get('PS_EU_PAYMENT_API'),
	    'legal_theme_dir' => $this->_legal ? $this->_legal->getCurrentThemeDir() : false
	));
    }
    
    public function setMedia() {
	parent::setMedia();
	
	$this->addJS(_MODULE_DIR_ . 'eu_legal/js/legal.js');
    }
    
    protected function _assignCarrier() {
	parent::_assignCarrier();
	
	$this->context->smarty->assign('PS_EU_PAYMENT_API', Configuration::get('PS_EU_PAYMENT_API')); 
    }
    
    protected function _getEuPaymentOptionsHTML() {
	$this->context->smarty->assign('payment_option', Tools::getValue('payment_option'));

	// Get available payment modules
	$payment_options_raw = Hook::exec('paymentEU', array(), null, true, true, false, null);

	$payment_options = array();
	
	if (is_array($payment_options_raw)) {
	    foreach($payment_options_raw as $module_name => $options) {
		if (is_array($options)) {
		    // One dimensional array: one payment option only
		    if (array_key_exists('cta_text', $options) && is_string($options['cta_text'])) {
			$payment_options[$module_name] = $options;
		    }
		    // Module returned several payment options
		    else {
			foreach ($options as $key => $option) {
			    $payment_options[$module_name.'_'.$key] = $option;
			}
		    }
		}
	    }
	}

	foreach ($payment_options as $option_name => &$option) {
	    if (isset($option['form'])) {
		$option['form'] = str_replace('@hiddenSubmit', "<input style='display:none' type='submit' id='submit_$option_name'>", $option['form']);
	    }
	}
	
	$payment_options = sizeof($payment_options) ? $payment_options : false;

	$this->context->smarty->assign('payment_options', $payment_options);
	$payment_options_html = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_ . '/' . $this->context->theme->directory . '/order-payment-eu.tpl');
	
	return $payment_options_html;
    }
    
    protected function _assignPayment() {
	// Invoke legally safe EU payment modules
	if ($PS_EU_PAYMENT_API = Configuration::get('PS_EU_PAYMENT_API')) {
	    // Within EU regulations, TOS acceptance is in last step, it's better!
	    $this->_assignWrappingAndTOS();

	    $payment_options_html = $this->_getEuPaymentOptionsHTML();

	    $this->context->smarty->assign(array(
		'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
		'HOOK_PAYMENT' => $payment_options_html,
		'PS_EU_PAYMENT_API' => $PS_EU_PAYMENT_API
	    ));
	}
	// Invoke legacy payment modules
	else {
	    $this->context->smarty->assign(array(
		'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
		'HOOK_PAYMENT' => Hook::exec('displayPayment'),
	    ));
	}
    }
}