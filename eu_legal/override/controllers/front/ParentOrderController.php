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

class ParentOrderController extends ParentOrderControllerCore
{
	protected $_legal = false;
	
	/*
	 * Construct the parent and instantiate the eu_legal module if it's installed and activated.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct()
	{
		/*
		* EU-Legal
		* instantiate EU Legal Module
		*/
		
		parent::__construct();
		
		$instance = Module::getInstanceByName('eu_legal');
	
		if (Validate::isLoadedObject($instance) && Module::isInstalled($instance->name) && Module::isEnabled($instance->name)) {
			$this->_legal = $instance;
		}
	}
    
	/*
	 * Get the instance of eu_legal module. A helper method to use in classes that are not children of ParentOrderController
	 *
	 * @access public
	 *
	 * @return object
	 */
	public function getLegalInstance()
	{
		/*
		* EU-Legal
		* instantiate EU Legal Module
		* (not used at the moment!)
		*/
		
		return $this->_legal;
	}
    
	/*
	 * Initialize parent and get two CMS pages responsible for "terms and conditions" and "terms of revocation".
	 * Also assigns PS_EU_PAYMENT_API var to use in templates and the path to eu_legal templates to use in
	 * smarty's "include" function.
	 *
	 * @access public
	 *
	 * @return void
	 */
	 public function init() 
	{
		parent::init();
		
		$cms = array(
			'PS_CONDITIONS_CMS_ID', 
			'LEGAL_CMS_ID_REVOCATION',
		);
	
		foreach ($cms as $config) {
			if ($id_cms = Configuration::get($config)) {
				$cms = new CMS((int)$id_cms, $this->context->language->id);
				
				if (Validate::isLoadedObject($cms)) {
					$this->context->smarty->assign($config, new CMS((int)$id_cms, $this->context->language->id));
					
					$link = $this->context->link->getCMSLink($cms, $cms->link_rewrite, Configuration::get('PS_SSL_ENABLED'));
					
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
    
	/*
	 * Adds a legal.js javascript file to parent's default javascript collection.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function setMedia() {
		parent::setMedia();
		
		$this->addJS(_MODULE_DIR_ . 'eu_legal/js/legal.js');
	}
	
	/*
	 * In addition to parent's _assignCarrier, assigns PS_EU_PAYMENT_API var to smarty to use in templates
	 *
	 * @access public
	 *
	 * @return void
	 */
	protected function _assignCarrier() {
		parent::_assignCarrier();
		
		$this->context->smarty->assign('PS_EU_PAYMENT_API', Configuration::get('PS_EU_PAYMENT_API')); 
	}
    
	/*
	 * Gets html templates for payment modules that are assigned to "displayPaymentEU" hook.
	 * This method is invoked only if "PS_EU_PAYMENT_API" is enabled.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function _getEuPaymentOptionsHTML() {
        if (!$this->isLogged)
            return '<p class="warning">'.Tools::displayError('Please sign in to see payment methods.').'</p>';
		$this->context->smarty->assign('payment_option', Tools::getValue('payment_option'));
	
		// Get available payment modules
		$payment_options_raw = Hook::exec('displayPaymentEU', array(), null, true, true, false, null);
	
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
	
		if ($this->_legal && $tpl = $this->_legal->getThemeOverride('order-payment-eu')) {
			$payment_options_html = $this->context->smarty->fetch($tpl);
		}
		else {
			$payment_options_html = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_ . '/' . $this->context->theme->directory . '/order-payment.tpl');
		}
	
		return $payment_options_html;
	}
    
	/*
	 * Assigns payment method templates to hooks that are responsible for payment methods display.
	 * Unlinke parent's method, it checks for "PS_EU_PAYMENT_API" - if it's enabled, only the modules
	 * hooked onto "displayPaymentEU" will be displayed. Otherwise it's a usualy display of modules
	 * hooked onto "displayPayment" hook.
	 *
	 * @access protected
	 *
	 * @return void
	 */
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
