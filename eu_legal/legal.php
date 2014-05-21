<?php

/**
* Legal
* Better security for german merchants.
* 
* @version       : 0.0.2
* @date          : 2014 05 07
* @author        : Markus Engel/Chris Gurk @ Onlineshop-Module.de | George June @ Silbersaiten.de
* @copyright     : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
* @contact       : info@onlineshop-module.de | info@silbersaiten.de
* @homepage      : www.onlineshop-module.de | www.silbersaiten.de
* @license       : http://opensource.org/licenses/osl-3.0.php
* @changelog     : see changelog.txt
* @compatibility : PS >= 1.6.0.6
*/

// no direct access to this module
if (!defined('_PS_VERSION_'))
	exit;
	
// main class
class Legal extends Module {
	
	/*******************************************************************************************************************
	*
	* Module vars and constants
	* (for source comments see __construct()
	*
	*******************************************************************************************************************/
	
	public $languages              = array();            
	public $default_language_id    = 1;               
	public $theme                  = array();
	public $deliveryNowDefault     = '';  
	public $deliveryLaterDefault   = ''; 
	public $hooks                  = array();
	public $modules_not_compatible = array();
	public $modules_must_install   = array();
	public $modules                = array();
	public $cms_pages              = array();
	public $config_prefix          = '';
	
	// Cache
	private static $_cms_pages = array();
	
	/*******************************************************************************************************************
	*
	* Construct // Module configuration
	*
	*******************************************************************************************************************/
	
	public function __construct() {
		
		// module name, must be same as class name and modul directory name
	 	$this->name = 'legal';                
	 	
		// module backoffice tab, maybe an other one?
		$this->tab = 'administration';       
	 	
		// version: major, minor, bugfix
		$this->version = '0.0.2';                
		
		// author
		$this->author = 'EU Legal Team'; 
		
		// instance? No
		$this->need_instance = 0;                      
		
		// module compliancy: only for exactly one PS version
		$this->ps_versions_compliancy = array(                  
			'min' => '1.6.0.4',
			'max' => '1.6.0.6'
		);
	 	
		// bootstrap baqckoffice functionality
		$this->bootstrap = true;                   
		
		parent::__construct();
		
		$this->displayName      = $this->l('Legal');
		$this->description      = $this->l('Better security for german merchants.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		
		// collect all available languages and default language
		$this->languages           = Language::getLanguages(false, false);
		$this->default_language_id = Configuration::get('PS_LANG_DEFAULT');
		
		foreach($this->languages as $key => $language)
			if($language['id_lang'] == $this->default_language_id)
				$this->languages[$key]['is_default'] = true;
			else
				$this->languages[$key]['is_default'] = false;
		
		// supportet theme
		// [theme name => theme title]
		$this->themes = array(           
			'bootstrap-legal' => $this->l('Bootstrap Legal'), 
		);
		
		// default values for delivery informations
		$this->deliveryNowDefault   = $this->l('1-3 Workdays');
		$this->deliveryLaterDefault = $this->l('7-10 Workdays');
		
		// new hooks to install
		$this->hooks = array(
			// reorder hook
			'displayReorder' => array(
				'name' => 'Reorder',
				'templates' => array(
					'history.tpl'
				),
			),
			// product delivery hook
			'displayProductAvailability' => array(
				'name' => 'Product Availability',
				'templates' => array(
					'product.tpl', 
					'product-list.tpl', 
					'products-comparison.tpl', 
					'shopping-cart-product-line.tpl'
				),
			),
			// product price hook
			'displayProductPriceBlock' => array(
				'name' => 'Product Price Display',
				'templates' => array(
					'product.tpl', 
					'product-list.tpl', 
					'products-comparison.tpl'
				),
			),
			'displayHeader' => array(
				'name' => 'display Header',
				'templates' => array(),
			),
		);
		
		// modules not compatible with legal
		$this->modules_not_compatible = array(
			'bankwire',
			'cheque',
			'blockcart',
		);
		
		// modules must install 
		$this->modules_must_install = array(
			'blockcustomerprivacy',
		);
		
		// supported modules, delivered with Legal
		$this->modules = array(           
			'gc_ganalytics' => 'Google Analytics',
			'gc_newsletter' => 'Newsletter',
			'gc_blockcart'  => 'Warenkorb Block',
		);
		
		// available cms pages
		// [filename => configuration]
		$this->cms_pages = array(
			array('name' => 'legalnotice',   'config' => 'LEGAL_CMS_ID_LEGAL',         'title' => $this->l('Legal Notice')),
			array('name' => 'conditions',    'config' => 'PS_CONDITIONS_CMS_ID',       'title' => $this->l('Conditions')),
			array('name' => 'revocation',    'config' => 'LEGAL_CMS_ID_REVOCATION',    'title' => $this->l('Revocation')),
			array('name' => 'privacy',       'config' => 'LEGAL_CMS_ID_PRIVACY',       'title' => $this->l('Privacy')),
			array('name' => 'environmental', 'config' => 'LEGAL_CMS_ID_ENVIRONMENTAL', 'title' => $this->l('Envorimental')),
			array('name' => 'shipping',      'config' => 'LEGAL_CMS_ID_SHIPPING',      'title' => $this->l('Shipping')),
		);
		
		// prefix for config vars
		$this->config_prefix = 'LEGAL_';
		
	}
	
	/*******************************************************************************************************************
	*
	* Install / Uninstall / Update
	*
	*******************************************************************************************************************/
	
	// install module
	public function install() {
		
		$return = true;
		
		// prescan overrides for existing functions. if some classes and functions already exists -> return error
		$this->_errors = array_merge($this->_errors, $this->checkOverrides());
		
		if(!empty($this->_errors))
			return false;
		
		// parent install (overrides, module itself, ...)
		if(!parent::install())
			return false;
		
		// install admin override templates
		$return &= $this->installAdminTemplates();
		
		// install and register hooks
		$return &= $this->installHooks();
		$return &= $this->installRegisterHooks();
		
		// global configuration values
		
		// shop specific configuration values
		if($return and !Configuration::updateValue('PS_TAX', true)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' PS_TAX';
		}
		if($return and !Configuration::updateValue('PS_TAX_DISPLAY', true)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' PS_TAX_DISPLAY';
		}
		if($return and !Configuration::updateValue('LEGAL_SHIPTAXMETH', true)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' LEGAL_SHIPTAXMETH';
		}
		if($return and !Configuration::updateValue('LEGAL_CONDITIONS_INPUT', 1)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' LEGAL_CONDITIONS_INPUT';
		}
		
		$values = array();
		foreach($this->languages as $language)
			$values[$language['id_lang']] = $this->deliveryNowDefault;
		if($return and !Configuration::updateValue('LEGAL_DELIVERY_NOW', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' LEGAL_DELIVERY_NOW';
		}
		
		
		$values = array(); 
		foreach($this->languages as $language)
			$values[$language['id_lang']] = $this->deliveryLaterDefault;
		if($return and !Configuration::updateValue('LEGAL_DELIVERY_LATER', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update config value:').' LEGAL_DELIVERY_LATER';
		}
		
		// set config vars for cms pages
		foreach($this->cms_pages as $cms_page)
			if(strpos($cms_page['config'], $this->config_prefix) === 0)
				if($return and !Configuration::updateValue($cms_page['config'], 0)) {
					$return &= false;
					$this->_errors[] = $this->l('Could not update config value:').' '.$cms_page['config'];
				}
		
		// add error translations
		if(is_file(_PS_TRANSLATIONS_DIR_.'de/errors.php')) {
			
			$rows_original = file(_PS_TRANSLATIONS_DIR_.'de/errors.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			
			$rows_new = file(_PS_MODULE_DIR_.$this->name.'/translations/de/errors.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			unset($rows_new[0]);
			
			$rows = array_merge($rows_original, $rows_new);
			
			if($return and !(bool)file_put_contents(_PS_TRANSLATIONS_DIR_.'de/errors.php', implode("\n\r", $rows))) {
				$return &= false;
				$this->_errors[] = $this->l('Could not update errors file');
			}
			
		}
		
		// alter database for price precitions
		if($return and !DB::getInstance()->execute("
			ALTER TABLE `"._DB_PREFIX_."orders` 
			CHANGE `total_discounts` `total_discounts` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_discounts_tax_incl` `total_discounts_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_discounts_tax_excl` `total_discounts_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid` `total_paid` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid_tax_incl` `total_paid_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid_tax_excl` `total_paid_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid_real` `total_paid_real` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_products` `total_products` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_products_wt` `total_products_wt` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_shipping` `total_shipping` DECIMAL(20,6) NOT NULL DEFAULT '0.00',
			CHANGE `total_shipping_tax_incl` `total_shipping_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00',
			CHANGE `total_shipping_tax_excl` `total_shipping_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00',
			CHANGE `total_wrapping` `total_wrapping` DECIMAL(20,6) NOT NULL DEFAULT '0.00',
			CHANGE `total_wrapping_tax_incl` `total_wrapping_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00',
			CHANGE `total_wrapping_tax_excl` `total_wrapping_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00'
		")) {
			$return &= false;
			$this->_errors[] = $this->l('Could not modify db table:').' '._DB_PREFIX_.'orders';
		}
		
		if($return and !DB::getInstance()->execute("
			ALTER TABLE `"._DB_PREFIX_."order_invoice` 
			CHANGE `total_discount_tax_excl` `total_discount_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_discount_tax_incl` `total_discount_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid_tax_excl` `total_paid_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_paid_tax_incl` `total_paid_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_products` `total_products` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_products_wt` `total_products_wt` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_shipping_tax_excl` `total_shipping_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_shipping_tax_incl` `total_shipping_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_wrapping_tax_excl` `total_wrapping_tax_excl` DECIMAL(20,6) NOT NULL DEFAULT '0.00', 
			CHANGE `total_wrapping_tax_incl` `total_wrapping_tax_incl` DECIMAL(20,6) NOT NULL DEFAULT '0.00'
		")) {
			$return &= false;
			$this->_errors[] = $this->l('Could not modify db table:').' '._DB_PREFIX_.'order_invoice';
		}
		
		// alter database for delivery time
		if($return and !$this->dbColumnExists('product_lang', 'delivery_now') and !$this->dbColumnExists('product_lang', 'delivery_later') and !DB::getInstance()->execute("
			ALTER TABLE `"._DB_PREFIX_."product_lang` 
			ADD `delivery_now` VARCHAR(255) NULL DEFAULT NULL AFTER `available_later`, 
			ADD `delivery_later` VARCHAR(255) NULL DEFAULT NULL AFTER `available_now`;
		")) {
			$return &= false;
			$this->_errors[] = $this->l('Could not modify db table:').' '._DB_PREFIX_.'product_lang';
		}
		
		// regenerate class index
		Autoload::getInstance()->generateIndex();
		
		return (bool)$return;
		
    }
	
	// install admin override templates
	protected function installAdminTemplates() {
		
		$return = true;
		
		if($return and !is_dir(_PS_OVERRIDE_DIR_.'controllers/admin/templates/products') and !@mkdir(_PS_OVERRIDE_DIR_.'controllers/admin/templates/products', 0755, true)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not create admin template dir.');
		}
		
		if($return and !@copy($this->local_path.'override/controllers/admin/templates/products/quantities.tpl', _PS_OVERRIDE_DIR_.'controllers/admin/templates/products/quantities.tpl')) {
			$this->_errors[] = $this->l('Could not copy admin templates.');
			$return &= false;
		}
		
		return $return;
		
	}
	
	// uninstall admin override templates
	protected function uninstallAdminTemplates() {
		
		return @unlink(_PS_OVERRIDE_DIR_.'controllers/admin/templates/products/quantities.tpl');
		
	}
	
	// Install all hooks from $this->hooks
	protected function installHooks() {
		
		$return = true;
		
		foreach($this->hooks as $hook_name => $hook) {
			
			if(Hook::getIdByName($hook_name))
				continue;
			
			$new_hook = new Hook();
			$new_hook->name = $hook_name;
			$new_hook->title = $hook['name'];
			$new_hook->position = true;
			$new_hook->live_edit = false;
			
			if(!$new_hook->add()) {
				$return &= false;
				$this->_errors[] = $this->l('Could not install new hook').': '.$hook_name;
			}
			
		}
		
		return $return;
		
	}
	
	// register this module to all hooks from $this->hooks
	protected function installRegisterHooks() {
		
		$return = true;
		
		foreach($this->hooks as $hook_name => $hook) {
			if(!$this->registerHook($hook_name)) {
				$return &= false;
				$this->_errors[] = $this->l('Could not register hook').': '.$hook_name;
			}
		}
		
		return $return;
		
	}
	
	// uninstall this module
	public function uninstall() {
		
		$return = true;
		
		// uninstall parent
		$return &= parent::uninstall();
		
		// global configuration
		
		// shop specific configuration
		$return &= Configuration::deleteByName('LEGAL_SHIPTAXMETH');
		$return &= Configuration::deleteByName('LEGAL_CONDITIONS_INPUT');
		$return &= Configuration::deleteByName('LEGAL_DELIVERY_NOW');
		$return &= Configuration::deleteByName('LEGAL_DELIVERY_LATER');
		
		foreach($this->cms_pages as $cms_page)
			if(strpos($cms_page['config'], $this->config_prefix) === 0)
				$return &= Configuration::deleteByName($cms_page['config']);
		
		// delete all delivery notes
		$configuration_ids = DB::getInstance()->executeS("
			SELECT c.`id_configuration` FROM `"._DB_PREFIX_."configuration` AS c
			WHERE c.`name` LIKE 'LEGAL_DN_%'
		");
		
		foreach($configuration_ids as $configuration_id) {
			$configuration = new Configuration($configuration_id['id_configuration']);
			$return &= $configuration->delete();
		}
		
		// restore daatabase structure
		if($return and $this->dbColumnExists('product_lang', 'delivery_now') and $this->dbColumnExists('product_lang', 'delivery_later') and !DB::getInstance()->execute("
			ALTER TABLE `"._DB_PREFIX_."product_lang` 
			DROP COLUMN `delivery_now`, 
			DROP COLUMN `delivery_later`;
		")) {
			$return &= false;
		}
		
		$this->uninstallAdminTemplates();
		
		// regenerate class index
		Autoload::getInstance()->generateIndex();
		
		return (bool)$return;
		
	}
	
	// reset this module without uninstall and install itself
	public function reset() {
		
		$return = true;
		
		// global configuration
		
		// shop specific configuration
		$return &= Configuration::updateValue('PS_TAX', true);
		$return &= Configuration::updateValue('PS_TAX_DISPLAY', true);
		$return &= Configuration::updateValue('LEGAL_SHIPTAXMETH', true);
		$return &= Configuration::updateValue('LEGAL_CONDITIONS_INPUT', 1);
		
		foreach($this->languages as $language) {
			$values[$language['id_lang']] = $this->deliveryNowDefault;
		}
		
		if(!Configuration::updateValue('LEGAL_DELIVERY_NOW', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update').': LEGAL_DELIVERY_NOW';
		}
		
		$values = array(); 
		
		foreach($this->languages as $language) {
			$values[$language['id_lang']] = $this->deliveryLaterDefault;
		}
		
		if(!Configuration::updateValue('LEGAL_DELIVERY_LATER', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update').': LEGAL_DELIVERY_LATER';
		}
		
		foreach($this->cms_pages as $cms_page)
			if(strpos($cms_page['config'], $this->config_prefix) === 0)
				$return &= Configuration::updateValue($cms_page['config'], 0);
		
		return (bool)$return;
		
	}
	
	/*******************************************************************************************************************
	*
	* Module Configuration
	*
	*******************************************************************************************************************/
	
	// module configuration
	public function getContent() {
		
		$html  = '';
		
		$this->context->controller->addCSS($this->_path.'views/css/admin/legal.css');
		
		if(!Tools::getIsset('addDeliverynote'))
			$html .= $this->displayInfo();
		
		$html .= $this->postProcess();
		
		$html .= $this->displayForm();
		
		return $html;
		
	}
	
	// display module logo and infos
	public function displayInfo() {
		
		$this->smarty->assign(array(
			'_path' =>		 $this->_path,
			'displayName' => $this->displayName,
			'author' =>      $this->author,
			'description' => $this->description,
		));
		 
		return $this->display(__FILE__, 'views/templates/module/info.tpl');
		
	}
	
	// display form
	public function displayForm() {
		
		$html = '';
		
		// delivery note add / update
		if(Tools::isSubmit('addDeliverynote') || Tools::isSubmit('updateDeliverynote')) {
			$html .= $this->displayFormDeliverynotes();
		}
		// all other configuration
		else {
			$html .= $this->displayFormSettings();
			$html .= $this->displayFormModules();
			$html .= $this->displayFormTheme();
			$html .= $this->displayListDeliverynotes();
		}
		
		return $html;
		
	}
	
	// general settings form
	protected function displayFormSettings() {
		
		$helper = new HelperOptions();
		
		// Helper Options
		$helper->required = false;
		$helper->id = Tab::getCurrentTabId(); //always Tab::getCurrentTabId() at helper option
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->table = '';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->identifier = null;           //alway null at helper option
		$helper->toolbar_btn = null;          //alway null at helper option
		$helper->ps_help_context = null;      //alway null at helper option
		$helper->title = null;                //alway null at helper option
		$helper->show_toolbar = true;         //alway true at helper option
		$helper->toolbar_scroll = false;      //alway false at helper option
		$helper->bootstrap = false;           //alway false at helper option
		
		$this->getOptionFieldsSettings();
		return $helper->generateOptions($this->option_fields_settings);
		
	}
	
	// additional modules form
	protected function displayFormModules() {
		
		$helper = new HelperForm();
		
		// Helper Form
		$helper->languages = $this->languages;
		$helper->default_form_language = $this->default_language_id;
		$helper->submit_action = 'submitAddModules';
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->table = 'configuration';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->title = null;
		
		foreach($this->modules as $name => $title) {
			$helper->fields_value[] = array('module_'.$name => (bool)Module::isInstalled($name));
		}
		
		$this->getFormFieldsModules();
		return $helper->generateForm($this->form_fields_modules);
		
	}
	
	// theme form
	protected function displayFormTheme() {
		
		$helper = new HelperForm();
		
		// Helper Form
		$helper->languages = $this->languages;
		$helper->default_form_language = $this->default_language_id;
		$helper->submit_action = 'submitSaveTheme';
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->table = 'configuration';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->title = null;
		
		$helper->fields_value['LEGAL_CSS'] = Configuration::get('LEGAL_CSS');
		
		$this->getFormFieldsTheme();
		return $helper->generateForm($this->form_fields_theme);
		
	}
	
	// delivery notes list
	protected function displayListDeliverynotes() {
		
		$helper = new HelperList();
		
		// Helper List
		$helper->shopLinkType = '';
		$helper->actions = array('edit', 'delete');
		$helper->toolbar_btn['new'] = array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&amp;token='.Tools::getAdminTokenLite('AdminModules').'&amp;addDeliverynote=1',
			'desc' => $this->l('Add'),
		);
		
		// Add icon: fixed bug in 1.6.0.5, no icon in list helper assigned
		Context::getContext()->smarty->assign(array('icon' => 'icon-truck'));
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->identifier = 'id_configuration';
		$helper->table = 'Deliverynote';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->title = $this->l('Delivery Notes');
		
		$fields_list = array(
			'carrier_name' => array(
				'title' => $this->l('Carrier'),
				
			),
			'module_name' => array(
				'title' => $this->l('Module'),
				
			),
			'zone_name' => array(
				'title' => $this->l('Zone'),
				
			),
			'note' => array(
				'title' => $this->l('Note'),
			),
		);
		
		$list_values = $this->getDeliveryNotes();
		
		return $helper->generateList($list_values, $fields_list);
		
	}
	
	// delivery notes form
	protected function displayFormDeliverynotes() {
		
		$helper = new HelperForm();
		
		// Helper Form
		$helper->languages = $this->languages;
		$helper->default_form_language = $this->default_language_id;
		$helper->submit_action = 'updateDeliverynote';
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->table = '';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->title = null;
		$helper->show_cancel_button = true;
		$helper->back_url = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
		
		if(Tools::isSubmit('id_configuration')) {
			$helper->fields_value = $this->getDeliveryNotes((int)Tools::getValue('id_configuration'));
		}
		else {
			$helper->fields_value = array(
				'id_carrier'       => 0,
				'id_module'        => 0,
				'id_zone'          => 0,
				'id_configuration' => 0,
			);
			foreach($this->languages as $language)
				$helper->fields_value['note'][$language['id_lang']] = '';
		}
		
		$this->getFormFieldsDeliverynote();
		return $helper->generateForm($this->form_fields_deliverynote);
		
	}
	
	protected function getOptionFieldsSettings() {
		
		$cms_pages = array();
		
		foreach($this->cms_pages as $cms_page) {
			$cms_pages[$cms_page['config']] = array(
				'type'       => 'select',
				'list'       => $this->getCMSPages(),
				'identifier' => 'id_cms',
				'title'      => $cms_page['title'],
			);
		}
		
		$this->option_fields_settings = array(
			array(
				'title' => $this->l('Global Settings'),
				'info' => $this->l('Global Settings for all shops'),
				'icon' => 'icon-globe',
				'fields' => array(
					'PS_EU_PAYMENT_API' => array(
						'type'  => 'bool',
						'title' => $this->l('EU Payment API Mode'),
						'desc'  => $this->l('Enable EU payment mode for payment modules. Note that it requires those modules to be specially designed.'),
						'auto_value' => false,
						'value' => Configuration::getGlobalValue('PS_EU_PAYMENT_API'),
						'no_multishop_checkbox' => true,
					),
				),
				'submit' => array(
					'title' => $this->l('Save global options'),
					'name' => 'submitSaveOptions',
				),
			),
			array(
				'title' => $this->l('General Options'),
				'info' => $this->l('General settings for your shop'),
				'icon' => 'icon-cog',
				'fields' => array(
					'LEGAL_SHIPTAXMETH' => array(
						'type'  => 'bool',
						'title' => $this->l('Shipping tax method'),
						'desc'  => $this->l('Calculates the average tax of all products for shipping instead of a fixed tax value.')
					),
					'LEGAL_DELIVERY_NOW' => array(
						'type'  => 'textLang',
						'title' => $this->l('Availabiliy "in stock"'),
						'desc'  => $this->l('Displayed text when in-stock default value. E.g.').' '.$this->deliveryNowDefault,
					),
					'LEGAL_DELIVERY_LATER' => array(
						'type'  => 'textLang',
						'title' => $this->l('Availabiliy "back-ordered"'),
						'desc'  => $this->l('Displayed text when allowed to be back-ordered default value. E.g.').' '.$this->deliveryLaterDefault,
					),
				),
				'submit' => array(
					'title' => $this->l('Save general options'),
					'name' => 'submitSaveOptions',
				),
			),
			array(
				'title' => $this->l('CMS Pages'),
				'info' => $this->l('Assign your CMS pages. Below you can add cms templates to your shop if they dont exists.'),
				'icon' => 'icon-pencil',
				'fields' => $cms_pages,
				'buttons' => array(
					array(
						'title' => $this->l('Add CMS Pages'),
						'name' => 'submitAddCMSPages',
						'type' => 'submit',
						'icon' => 'process-icon-plus',
					),
				),
				'submit' => array(
					'title' => $this->l('Save CMS assignment'),
					'name' => 'submitSaveOptions',
				),
			),
		);
		
	}
	
	protected function getFormFieldsModules() {
		
		$modules_must_install = '';
		$modules_not_compatible =  '';
		
		foreach($this->modules_must_install as $module) {
			
			if(Module::isInstalled($module))
				if(Module::isEnabled($module))
					continue;
				else
					$modules_must_install .= $this->l('Must enable').': <b>'.$module.'</b><br>';
			else
				$modules_must_install .= $this->l('Please install and enable').': <b>'.$module.'</b><br>';
			
		}
		
		foreach($this->modules_not_compatible as $module) {
			
			if(!Module::isEnabled($module))
				continue;
			else
				$modules_not_compatible .= $this->l('Please disable').': <b>'.$module.'</b><br>';
			
		}
		
		$modules = array();
		foreach($this->modules as $name => $title) {
			$modules[] = array(
				'name'      => $name,
				'title'     => $title,
				'installed' => (bool)Module::isInstalled($name),
				'val'       => $name,
			);
		}
		
		$this->form_fields_modules = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Additional Modules'),
						'icon' => 'icon-puzzle-piece'
					),
					'input' => array(
						/*array(
							'type' => 'checkbox_module',
							'label' => $this->l('Must have modules'),
							'name' => 'modules',
							'desc'  => $this->l('These are additional modules served with Legal. Please install them.'),
							'values' => array(
								'query'    => $modules,
								'id'       => 'name',
								'name'     => 'title',
								'disabled' => 'installed',
							),
						),*/
						array(
							'type'  => 'html',
							'id'    => 'modules_not_compatible',
							'label' => $this->l('Modules not compatible'),
							'name'  => (!empty($modules_not_compatible) ? '<div class="alert alert-warning">'.$modules_not_compatible.'</div>' : '<div class="alert alert-success">'.$this->l('There are no modules not compatible to Legal installed.').'</div>'),
							'desc'  => $this->l('You have to uninstall some modules not compatible with Legal.'),
						),
						array(
							'type'  => 'html',
							'id'    => 'modules_must_install',
							'label' => $this->l('Must install modules'),
							'name'  => (!empty($modules_must_install) ? '<div class="alert alert-warning">'.$modules_must_install.'</div>' : '<div class="alert alert-success">'.$this->l('You have installes and enabled all required modules.').'</div>'),
							'desc'  => $this->l('You have to install some required prestashop modules.'),
						),
					),
					'submit' => array(
						'title' => $this->l('Add Modules'),
						'icon'  => 'process-icon-plus',
					)
				),
			),
		);
		
	}
	
	protected function getFormFieldsTheme() {
		
		$this->form_fields_theme = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Template Settings'),
						'icon' => 'icon-picture-o'
					),
					'input' => array(
						array(
							'type'  => 'switch',
							'label' => $this->l('Legal CSS'),
							'name'  => 'LEGAL_CSS',
							'desc'  => $this->l('Activate the provides Legal CSS file for your theme.'),
							'values' => array(
								array(
									'value' => 1,
								),
								array(
									'value' => 0,
								),
							),
						),
					),
					'submit' => array(
						'title' => $this->l('Save theme settings'),
						'icon'  => 'process-icon-plus',
					)
				),
			),
		);
		
		foreach($this->searchHooksInThemes() as $theme => $missing_hook) {
			
			$missing_hooks = '';
			
			$missing_hooks .= 'Theme <i>'.$theme.'</i>: ';
			$missing_hooks .= '<ul>';
			
			foreach($missing_hook as $template => $hooks) {
				
				$missing_hooks .= '<li>Template <i>'.$template.'</i>: ';
				$missing_hooks .= '<ul>';
				
				foreach($hooks as $hook) {
					$missing_hooks .= '<li>Hook <i>'.$hook.'</i></li>';
				}
				
				$missing_hooks .= '</li></ul>';
				
			}
			
			$missing_hooks .= '</ul>';
			
			$this->form_fields_theme[0]['form']['input'][] = array(
				'type'  => 'html',
				'label' => sprintf($this->l('Hooks in theme "%s"'), $theme),
				'id'    => 'missing_hooks',
				'name'  => (!empty($missing_hooks) ? '<div class="alert alert-warning"><b>'.$this->l('There are some missing hooks!').'</b><br>'.$missing_hooks.'</div>' : '<div class="alert alert-success">'.$this->l('There are all hooks in your themes available.').'</div>'),
				'desc'  => $this->l('Are all Legal hooks available in your themes?'),
			);
			
		}
		
	}
	
	protected function getFormFieldsDeliverynote() {
		
		$this->form_fields_deliverynote = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Add Delivery Note'),
						'icon' => 'icon-truck'
					),
					'input' => array(
						array(
							'type' => 'select',
							'label' => $this->l('Carrier'),
							'name' => 'id_carrier',
							'options' => array(
								'query'    => Carrier::getCarriers($this->context->cookie->id_lang),
								'id'       => 'id_carrier',
								'name'     => 'name',
								'default' => array(
									'value' => '0',
									'label' => $this->l('-- Please select a carrier --'),
								),
							),
							'required' => true,
						),
						array(
							'type' => 'select',
							'label' => $this->l('Module'),
							'name' => 'id_module',
							'options' => array(
								'query'    => PaymentModule::getInstalledPaymentModules(),
								'id'       => 'id_module',
								'name'     => 'name',
								'default' => array(
									'value' => '0',
									'label' => $this->l('-- Please select a module --'),
								),
							),
							'required' => true,
						),
						array(
							'type' => 'select',
							'label' => $this->l('Zone'),
							'name' => 'id_zone',
							'options' => array(
								'query'    => Zone::getZones(),
								'id'       => 'id_zone',
								'name'     => 'name',
								'default' => array(
									'value' => '0',
									'label' => $this->l('-- Please select a zone --'),
								),
							),
							'required' => true,
						),
						array(
							'type' => 'textarea',
							'label' => $this->l('Note'),
							'name' => 'note',
							'lang' => true,
							'required' => true,
						),
						array(
							'type' => 'hidden',
							'name' => 'id_configuration',
						),
					),
					'submit' => array(
						'title' => $this->l('Save'),
						'name' => 'submitAddDeliverynote',
					)
				),
			),
		);
		
	}
	
	/*******************************************************************************************************************
	*
	* Post Process
	*
	*******************************************************************************************************************/
	
	public function postProcess() {
		
		$this->_errors = array();
		
		// Generelle Einstellungen
		if (Tools::isSubmit('submitSaveOptions')) {
			
			// Generelle Einstellungen
			
			if(!Configuration::updateValue('LEGAL_SHIPTAXMETH', (bool)Tools::getValue('LEGAL_SHIPTAXMETH'))) 
				$this->_errors[] = $this->l('Could not update').': LEGAL_SHIPTAXMETH';
				
			if(!Configuration::updateValue('LEGAL_CONDITIONS_INPUT', (int)Tools::getValue('LEGAL_CONDITIONS_INPUT'))) 
				$this->_errors[] = $this->l('Could not update').': LEGAL_CONDITIONS_INPUT';
			
			// Produktverfügbarkeit
			$values = array(); 
			
			foreach($this->languages as $language) {
				$values[$language['id_lang']] = Tools::getValue('LEGAL_DELIVERY_NOW_'.$language['id_lang']);
			}
			
			if(!Configuration::updateValue('LEGAL_DELIVERY_NOW', $values))
				$this->_errors[] = $this->l('Could not update').': LEGAL_DELIVERY_NOW';
			
			$values = array(); 
			
			foreach($this->languages as $language) {
				$values[$language['id_lang']] = Tools::getValue('LEGAL_DELIVERY_LATER_'.$language['id_lang']);
			}
			
			if(!Configuration::updateValue('LEGAL_DELIVERY_LATER', $values))
				$this->_errors[] = $this->l('Could not update').': LEGAL_DELIVERY_LATER';
			
			// CMS IDs festlegen
			if(!Configuration::updateValue('LEGAL_CMS_ID_LEGAL', (int)Tools::getValue('LEGAL_CMS_ID_LEGAL')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CMS_ID_LEGAL';
			
			if(!Configuration::updateValue('PS_CONDITIONS_CMS_ID', (int)Tools::getValue('PS_CONDITIONS_CMS_ID')))
				$this->_errors[] = $this->l('Could not update').': PS_CONDITIONS_CMS_ID';
			
			if(!Configuration::updateValue('LEGAL_CMS_ID_REVOCATION', (int)Tools::getValue('LEGAL_CMS_ID_REVOCATION')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CMS_ID_REVOCATION';
			
			if(!Configuration::updateValue('LEGAL_CMS_ID_PRIVACY', (int)Tools::getValue('LEGAL_CMS_ID_PRIVACY')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CMS_ID_PRIVACY';
			
			if(!Configuration::updateValue('LEGAL_CMS_ID_ENVIRONMENTAL', (int)Tools::getValue('LEGAL_CMS_ID_ENVIRONMENTAL')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CMS_ID_ENVIRONMENTAL';
			
			if(!Configuration::updateValue('LEGAL_CMS_ID_SHIPPING', (int)Tools::getValue('LEGAL_CMS_ID_SHIPPING')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CMS_ID_SHIPPING';
			
			if(!Configuration::updateGlobalValue('PS_EU_PAYMENT_API', (bool)Tools::getValue('PS_EU_PAYMENT_API')))
				$this->_errors[] = $this->l('Could not update').': PS_EU_PAYMENT_API';
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Settings updated'));
			
		}
		
		elseif (Tools::isSubmit('submitAddCMSPages')) {
			
			// install all cms pages
			foreach($this->cms_pages as $cms_page) {
				
				if($content = @file_get_contents($this->local_path.'cms/'.$cms_page['name'].'.txt')) {
					
					$cms = new CMS();
					$cms->active = true;
					$cms->id_cms_category = 1;
					
					$content = preg_replace('#src="(.*)"#u', 'src="'.Context::getContext()->shop->getBaseURL().'\\1"', $content);
					
					foreach($this->languages as $language) {
						
						$cms->meta_title[$language['id_lang']]       = $cms_page['title'];
						$cms->meta_description[$language['id_lang']] = $cms_page['title'];
						$cms->meta_keywords[$language['id_lang']]    = $cms_page['title'];
						$cms->link_rewrite[$language['id_lang']]     = Tools::link_rewrite($cms_page['title']);
						$cms->content[$language['id_lang']]          = trim($content);
						
					}
					
					if(!$cms->add())
						$this->_errors[] = $this->l('Could not add new cms page').': '.$cms_page['name'];
					
					Configuration::updateValue($cms_page['config'], $cms->id);
					
					$_POST[$cms_page['config']] = $cms->id;
					
				}
				else
					$this->_errors[] = $this->l('Could not open file').': cms/'.$cms_page['name'].'.txt';
				
			}
			
			// copy cms images
			try {
				$this->rcopy('modules/'.$this->name.'/img/cms/', 'img/cms/', array('root' => _PS_ROOT_DIR_));
			}
			catch(Exception $e) {
				$this->_errors[] = $this->l('Could not copy').': /img/cms/';
			}
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('CMS Pages created'));
			
		}
		
		elseif (Tools::isSubmit('submitSaveTheme')) {
			
			if(!Configuration::updateValue('LEGAL_CSS', (bool)Tools::getValue('LEGAL_CSS')))
				$this->_errors[] = $this->l('Could not update').': LEGAL_CSS';
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Theme settings saved'));
			
		}
		
		elseif(Tools::isSubmit('submitAddDeliverynote')) {
			
			$notes = array();
			
			$id_carrier = (int)Tools::getValue('id_carrier');
			$id_module  = (int)Tools::getValue('id_module');
			$id_zone    = (int)Tools::getValue('id_zone');
			
			$id_configuration = (int)Tools::getValue('id_configuration');
			
			foreach($this->languages as $language) {
				$notes[$language['id_lang']] = Tools::getValue('note_'.$language['id_lang']);
			}
			
			if($id_configuration) {
				
				$configuration = new Configuration($id_configuration);
				$configuration->delete();
				
			}
			
			if(!Configuration::updateValue('LEGAL_DN_'.$id_carrier.'_'.$id_module.'_'.$id_zone, $notes))
				$this->_errors[] = $this->l('Could not update').': LEGAL_DN_'.$id_carrier.'_'.$id_module.'_'.$id_zone;
			
			if(count($this->_errors) <= 0)
				Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
			
		}
		
		elseif(Tools::isSubmit('deleteDeliverynote')) {
			
			$id_configuration = (int)Tools::getValue('id_configuration');
			$configuration = new Configuration($id_configuration);
			
			if(Validate::isLoadedObject($configuration))
				$configuration->delete();
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Delivery note deleted'));
			
		}
		
		if(!empty($this->_errors))
			return $this->displayError(implode('<br>', $this->_errors));
			
		return '';
		
	}
	
	/*******************************************************************************************************************
	*
	* Helper Functions
	*
	*******************************************************************************************************************/
	
	private function searchHooksInThemes() {
		
		$themes = Theme::getThemes();
		
		$not_found_hooks = array();
		
		foreach($themes as $theme) {
			
			if(!$theme->isUsed())
				continue;
			
			$this->searchHooksInTheme($theme->directory, $not_found_hooks);
			
		}
		
		return $not_found_hooks;
		
	}
	
	private function searchHooksInTheme($theme, &$not_found_hooks) {
		
		foreach($this->hooks as $hook_name => $hook) {
			
			$search = '{hook h="'.$hook_name.'"';
			
			foreach($hook['templates'] as $template) {
				
				$content = '';
				
				$content = Tools::file_get_contents(_PS_ALL_THEMES_DIR_.$theme.'/'.$template);
				
				if(!$content or (strstr($content, $search) === false))
					$not_found_hooks[$theme][$template][] = $hook_name;
				
			}
			
		}
		
	}
	
	private function getCMSPages() {
		
		if(empty(self::$_cms_pages)) {
			
			$result = CMS::getCMSPages($this->default_language_id, null, false);
			
			self::$_cms_pages[] = array('id_cms' => 0, 'name' => $this->l('-- Please select a CMS page --'));
			
			foreach($result as $key => $row)
				self::$_cms_pages[] = array('id_cms' => $row['id_cms'], 'name' => $row['meta_title']);
			
		}
		
		return self::$_cms_pages;
		
	}
	
	public function rcopy($src, $dest, $options=array()) {
		
		/**
		* function rcopy()
		* @author: Markus Engel @ Presta-Profi.de
		* @release: 1.1
		* @mail: info@presta-profi.de
		* @www: presta-profi.de
		* 
		* @return: true if success, error exception if not
		* @params:
		*  src: string, source file/dir name, required
		*  dest: string, destination file/dir name, required
		*  options: array, optional
		*   file_permission
		*   dir_permission
		*   root
		*   ds
		* @license: CC BY-NC-SA 4.0 http://creativecommons.org/licenses/by-nc-sa/4.0/
		*/
		
		if(empty($src)) {
			throw new Exception('Source is empty.');
		}
		
		if(empty($dest)) {
			throw new Exception('Destination is empty.');
		}
		
		$options_default = array(
			'file_permission' => 0644,
			'dir_permission'  => 0755,
			'root'            => '/',
			'ds'              => '/'
		);
		
		$options = array_merge($options_default, $options);
		
		$is_win = (DIRECTORY_SEPARATOR == '\\');
		
		if($is_win) {
			$src = strtr($src, '\\', '/');
			$dest = str_replace('\\', '/', $dest);
			$options['root'] = str_replace('\\', '/', $options['root']);
			
			$src = preg_replace('#[a-z]{1}:#i', '', $src);
			$dest = preg_replace('#[a-z]{1}:#i', '', $dest);
			$options['root'] = preg_replace('#[a-z]{1}:#i', '', $options['root']);
		}
		
		if(!preg_match('#0[1-7]{3}#', sprintf("%o", $options['file_permission'])))
			$options['file_permission'] = $options_default['file_permission'];
		
		if(!preg_match('#0[1-7]{3}#', sprintf("%o", $options['dir_permission'])))
			$options['dir_permission'] = $options_default['dir_permission'];
		
		if(!in_array($options['ds'], array('/', '\\')))
			$options['ds'] = $options_default['ds'];
		
		// DS vom Ende entfernen
		if(substr($src, -1) == $options['ds']) $src = substr($src, 0, -1);
		if(substr($dest, -1) == $options['ds']) $dest = substr($dest, 0, -1);
		
		// DS vom Anfang entfernen
		if(substr($src, 0, 1) == $options['ds']) $src = substr($src, 1);
		if(substr($dest, 0, 1) == $options['ds']) $dest = substr($dest, 1);
		
		// DS am Ende hinzufügen
		if(substr($options['root'], -1) != $options['ds']) $options['root'] = $options['root'].$options['ds'];
		
		// DS am Anfang hinzufügen
		if(substr($options['root'], 0, 1) != $options['ds']) $options['root'] = $options['ds'].$options['root'];
		
		if(is_link($options['root'].$src)) {
			
			if(!symlink(readlink($options['root'].$src), $options['root'].$dest))
				throw new Exception('Can not create symlink from source: '.$options['root'].$src);
			
		}
		elseif(is_file($options['root'].$src)) {
			
			if(!copy($options['root'].$src, $options['root'].$dest))
				throw new Exception('Can not copy file from source: '.$options['root'].$src);
			
			if(!$is_win)
				chmod($options['root'].$dest, $options['file_permission']);
			
		}
		elseif(is_dir($options['root'].$src)) {
			
			if(is_file($options['root'].$dest) or is_link($options['root'].$dest)) {
				throw new Exception('Destination must be a directory: '.$options['root'].$dest);
			}
			elseif(!is_dir($options['root'].$dest) and !mkdir($options['root'].$dest, (!$is_win ? $options['dir_permission'] : null))) {
				throw new Exception('Can not create destination directory: '.$options['root'].$dest);
			}
			
			if(!$dir = dir($options['root'].$src))
				throw new Exception('Can not open directory: '.$options['root'].$src);
			
			while (false !== ($entry = $dir->read())) {
				
				if ($entry == '.' || $entry == '..')
					continue;
				
				$this->rcopy($src.$options['ds'].$entry, $dest.$options['ds'].$entry, $options);
				
			}
			
			$dir->close();
			
		}
		else {
			throw new Exception('No file or directory: '.$options['root'].$src);
		}
		
		return true;
		
	}
	
	private function getDeliveryNotes($id_configuration = false) {
		
		$return = array();
		
		$id_configuration = (int)$id_configuration;
		
		if($id_configuration)
			$sql = "
				SELECT c.`id_configuration` FROM `"._DB_PREFIX_."configuration` AS c
				WHERE c.`id_configuration` = '".$id_configuration."'
			";
		else
			$sql = "
				SELECT c.`id_configuration` FROM `"._DB_PREFIX_."configuration` AS c
				WHERE c.`name` LIKE 'LEGAL_DN_%'
			";
		
		$configuration_ids = DB::getInstance()->executeS($sql);
		
		foreach($configuration_ids as $configuration_id) {
			
			$configuration = new Configuration($configuration_id['id_configuration']);
			
			if(!$configuration)
				continue;
			
			$m = array();
			
			if(preg_match('#LEGAL_DN_([0-9]+)_([0-9]+)_([0-9]+)#', $configuration->name, $m)) {
				
				$carrier = new Carrier($m[1], $this->context->cookie->id_lang);
				$module  = Module::getInstanceById($m[2]);
				$zone    = new Zone($m[3]);
				
				if(Validate::isLoadedObject($carrier))
					$carrier_name = $carrier->name;
				else
					$carrier_name = '*';
				
				if(Validate::isLoadedObject($module))
					$module_name = $module->displayName;
				else
					$module_name = '*';
				
				if($id_configuration) {
					
					$return = array(
						'id_configuration' => $configuration->id,
						'id_carrier'       => $m[1],
						'carrier_name'     => $carrier_name,
						'id_module'        => $module->id,
						'module_name'      => $module_name,
						'id_zone'          => $m[2],
						'zone_name'        => $zone->name,
					);
					
					foreach($this->languages as $language)
						if(isset($configuration->value[$language['id_lang']]))
							$return['note'][$language['id_lang']] = $configuration->value[$language['id_lang']];
						else
							$return['note'][$language['id_lang']] = '';
					
				}
				else {
					
					$return[$configuration->id] = array(
						'id_configuration' => $configuration->id,
						'id_carrier'       => $m[1],
						'carrier_name'     => $carrier_name,
						'id_module'        => $module->id,
						'module_name'      => $module_name,
						'id_zone'          => $m[2],
						'zone_name'        => $zone->name,
						'note'             => $configuration->value[$this->default_language_id],
					);
					
				}
				
			}
			
		}
		
		return $return;
		
	}
	
	public function checkOverrides() {
		
		$errors = array();
		
		Autoload::getInstance()->generateIndex();
		
		foreach (Tools::scandir($this->getLocalPath().'override', 'php', '', true) as $file) {
			$class = basename($file, '.php');
			
			if (Autoload::getInstance()->getClassPath($class.'Core') and $error = $this->checkOverride($class))
				$errors[] = $error;
		}
		
		return $errors;
		
	}
	
	public function checkOverride($classname) {
		
		$path = PrestaShopAutoload::getInstance()->getClassPath($classname.'Core');
		
		// Check if there is already an override file
		if (!($classpath = PrestaShopAutoload::getInstance()->getClassPath($classname))) {
			
			$override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.$path;
			
			if (!is_writable(dirname($override_dest)))
				return sprintf(Tools::displayError('directory (%s) not writable'), dirname($override_dest));
			
			return '';
			
		}
		
		// Check if override file is writable
		$override_path = _PS_ROOT_DIR_.'/'.PrestaShopAutoload::getInstance()->getClassPath($classname);
		
		// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
		do $uniq = uniqid();
		while (class_exists($classname.'OverrideOriginal'.$uniq, false));

		if (!is_writable($override_path))
			return sprintf(Tools::displayError('file (%s) not writable'), $override_path);
		
		// Make a reflection of the override class and the module override class
		$override_file = file($override_path);
		
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal'.$uniq), implode('', $override_file)));
		$override_class = new ReflectionClass($classname.'OverrideOriginal'.$uniq);

		$module_file = file($this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
		$module_class = new ReflectionClass($classname.'Override'.$uniq);

		// Check if none of the methods already exists in the override class
		foreach ($module_class->getMethods() as $method)
			if ($override_class->hasMethod($method->getName()))
				return sprintf(Tools::displayError('The method %1$s in the class %2$s is already overriden.'), $method->getName(), $classname);

		// Check if none of the properties already exists in the override class
		foreach ($module_class->getProperties() as $property)
			if ($override_class->hasProperty($property->getName()))
				return sprintf(Tools::displayError('The property %1$s in the class %2$s is already defined.'), $property->getName(), $classname);
		
		// Check if none of the constants already exists in the override class
		foreach ($module_class->getConstants() as $constant => $value)
			if ($override_class->hasConstant($constant))
				return sprintf(Tools::displayError('The constant %1$s in the class %2$s is already defined.'), $constant, $classname);
		
		return '';
		
	}
	
	public function deleteOverride($classname, $function) {
		
		if (!PrestaShopAutoload::getInstance()->getClassPath($classname))
			return true;

		// Check if override file is writable
		$override_path = _PS_ROOT_DIR_.'/'.PrestaShopAutoload::getInstance()->getClassPath($classname);
		if (!is_writable($override_path))
			return false;

		// Make a reflection of the override class and the module override class
		$override_file = file($override_path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal_delete'), implode('', $override_file)));
		$override_class = new ReflectionClass($classname.'OverrideOriginal_delete');

		// Remove methods from override file
		$override_file = file($override_path);
		
		if (!$override_class->hasMethod($function))
			return false;

		$method = $override_class->getMethod($function);
		$length = $method->getEndLine() - $method->getStartLine() + 1;
		array_splice($override_file, $method->getStartLine() - 1, $length, array_pad(array(), $length, '#--remove--#'));

		// Rewrite nice code
		$code = '';
		
		foreach ($override_file as $line)
		{
			if ($line == '#--remove--#')
				continue;

			$code .= $line;
		}
		
		file_put_contents($override_path, $code);

		return true;
		
	}
	
	// Nur temporär, kann in zukünftigen Versionen entfernt werden. Problem mit Upgrade und Overrides
	public function addOverride($classname) {
		
		/*
		* Legal 0.0.1 | 20140324
		* Die Do-While schleife benötigt uniqid als Argument
		*/
		
		$path = PrestaShopAutoload::getInstance()->getClassPath($classname.'Core');

		// Check if there is already an override file, if not, we just need to copy the file
		if (PrestaShopAutoload::getInstance()->getClassPath($classname))
		{
			// Check if override file is writable
			$override_path = _PS_ROOT_DIR_.'/'.PrestaShopAutoload::getInstance()->getClassPath($classname);
			if ((!file_exists($override_path) && !is_writable(dirname($override_path))) || (file_exists($override_path) && !is_writable($override_path)))
				throw new Exception(sprintf(Tools::displayError('file (%s) not writable'), $override_path));

			// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
			do $uniq = uniqid();
			while (class_exists($classname.'OverrideOriginal'.$uniq, false));
				
			// Make a reflection of the override class and the module override class
			$override_file = file($override_path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal'.$uniq), implode('', $override_file)));
			$override_class = new ReflectionClass($classname.'OverrideOriginal'.$uniq);

			$module_file = file($this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
			$module_class = new ReflectionClass($classname.'Override'.$uniq);

			// Check if none of the methods already exists in the override class
			foreach ($module_class->getMethods() as $method)
				if ($override_class->hasMethod($method->getName()))
					throw new Exception(sprintf(Tools::displayError('The method %1$s in the class %2$s is already overriden.'), $method->getName(), $classname));

			// Check if none of the properties already exists in the override class
			foreach ($module_class->getProperties() as $property)
				if ($override_class->hasProperty($property->getName()))
					throw new Exception(sprintf(Tools::displayError('The property %1$s in the class %2$s is already defined.'), $property->getName(), $classname));
			
			// Check if none of the constants already exists in the override class
			foreach ($module_class->getConstants() as $constant => $value)
				if ($override_class->hasConstant($constant))
					throw new Exception(sprintf(Tools::displayError('The constant %1$s in the class %2$s is already defined.'), $constant, $classname));
			
			// Insert the methods from module override in override
			$copy_from = array_slice($module_file, $module_class->getStartLine() + 1, $module_class->getEndLine() - $module_class->getStartLine() - 2);
			array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
			$code = implode('', $override_file);
			file_put_contents($override_path, $code);
		}
		else
		{
			$override_src = $this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$path;
			$override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.$path;
			if (!is_writable(dirname($override_dest)))
				throw new Exception(sprintf(Tools::displayError('directory (%s) not writable'), dirname($override_dest)));
			copy($override_src, $override_dest);
			// Re-generate the class index
			PrestaShopAutoload::getInstance()->generateIndex();
		}
		return true;
	}
	
	// Nur temporär, kann in zukünftigen Versionen entfernt werden. Problem mit Upgrade und Overrides
	public function removeOverride($classname) {
		
		/*
		* Legal 0.0.1 | 20140324
		* Die Do-While schleife benötigt uniqid als Argument
		* Bei löschen von overrides müssen auch Klassenkostanten 'const' entfernt werden
		*/
		
		$path = PrestaShopAutoload::getInstance()->getClassPath($classname.'Core');

		if (!PrestaShopAutoload::getInstance()->getClassPath($classname))
			return true;

		// Check if override file is writable
		$override_path = _PS_ROOT_DIR_.'/'.PrestaShopAutoload::getInstance()->getClassPath($classname);
		if (!is_writable($override_path))
			return false;
		
		// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
		do $uniq = uniqid();
		while (class_exists($classname.'OverrideOriginal_remove'.$uniq, false));
		
		// Make a reflection of the override class and the module override class
		$override_file = file($override_path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal_remove'.$uniq), implode('', $override_file)));
		$override_class = new ReflectionClass($classname.'OverrideOriginal_remove'.$uniq);

		$module_file = file($this->getLocalPath().'override/'.$path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override_remove'.$uniq), implode('', $module_file)));
		$module_class = new ReflectionClass($classname.'Override_remove'.$uniq);

		// Remove methods from override file
		$override_file = file($override_path);
		foreach ($module_class->getMethods() as $method)
		{
			if (!$override_class->hasMethod($method->getName()))
				continue;

			$method = $override_class->getMethod($method->getName());
			$length = $method->getEndLine() - $method->getStartLine() + 1;
			
			$module_method = $module_class->getMethod($method->getName());
			$module_length = $module_method->getEndLine() - $module_method->getStartLine() + 1;

			$override_file_orig = $override_file;

			$orig_content = preg_replace("/\s/", '', implode('', array_splice($override_file, $method->getStartLine() - 1, $length, array_pad(array(), $length, '#--remove--#'))));
			$module_content = preg_replace("/\s/", '', implode('', array_splice($module_file, $module_method->getStartLine() - 1, $length, array_pad(array(), $length, '#--remove--#'))));

			if (md5($module_content) != md5($orig_content))
				$override_file = $override_file_orig;
		}

		// Remove properties from override file
		foreach ($module_class->getProperties() as $property)
		{
			if (!$override_class->hasProperty($property->getName()))
				continue;

			// Remplacer la ligne de declaration par "remove"
			foreach ($override_file as $line_number => &$line_content)
				if (preg_match('/(public|private|protected)\s+(static\s+)?\$'.$property->getName().'/i', $line_content))
				{
					$line_content = '#--remove--#';
					break;
				}
		}
		
		// Remove constants from override file
		foreach ($module_class->getConstants() as $constant => $value)
		{
			if (!$override_class->hasConstant($constant))
				continue;

			// Remplacer la ligne de declaration par "remove"
			foreach ($override_file as $line_number => &$line_content)
				if (preg_match('/(const)\s+'.$constant.'/i', $line_content))
				{
					$line_content = '#--remove--#';
					break;
				}
		}
		
		// Rewrite nice code
		$code = '';
		foreach ($override_file as $line)
		{
			if ($line == '#--remove--#')
				continue;

			$code .= $line;
		}
		file_put_contents($override_path, $code);

		return true;
	}
	
	public function assignCMSPages() {
		
		foreach($this->cms_pages as $cms_page) {
			
			$link = Context::getContext()->link->getCMSLink(Configuration::get($cms_page['config']), null, true);
			
			if(strpos($link, '?'))
				$link = $link.'&content_only=1';
			else
				$link = $link.'?content_only=1';
			
			Context::getContext()->smarty->assign(array(
				$cms_page['name'] => Configuration::get($cms_page['config']),
				'link_'.$cms_page['name'] => $link
			));
			
		}
		
	}
	
	protected function dbColumnExists($table_name, $column_name) {
		
		$result = DB::getInstance()->executeS("
			SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME = '"._DB_PREFIX_.pSQL($table_name)."'
			AND TABLE_SCHEMA = '"._DB_NAME_."'
		");
		
		if(!$result)
			return null;
		
		foreach($result as $row) {
			
			if($row['COLUMN_NAME'] == $column_name)
				return true;
			
		}
		
		return false;
		
	}
	
	/*******************************************************************************************************************
	*
	* Hooks
	*
	*******************************************************************************************************************/
	
	public function hookDisplayHeader($params) {
		
		if(Configuration::get('LEGAL_CSS'))
			$this->context->controller->addCss($this->_path.'views/css/front/legal.css');
		
		$this->assignCMSPages();
		
		$this->context->smarty->assign(array(
			'legal' => 1,
		));
		
	}
	
	public function hookDisplayReorder($params) {
		
		return $this->display(__FILE__, 'displayReorder.tpl');
		
	}
	
	public function hookDisplayProductAvailability($params) {
		
		if(!isset($params['product']))
			return;
		
		$this->smarty->assign(array(
			'product'               => $params['product'],
			'priceDisplay'          => Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer),
			'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
		));
		
		return $this->display(__FILE__, 'displayProductAvailability.tpl');
		
	}
	
	public function hookDisplayProductPriceBlock($params) {
		
		if(!isset($params['product']))
			return;
		
		$this->smarty->assign(array(
			'product'               => $params['product'],
			'priceDisplay'          => Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer),
			'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
			'php_self'              => $this->context->controller->php_self,
			'tax_enabled'           => Configuration::get('PS_TAX'),  
			'cms_id_shipping'       => Configuration::get('LEGAL_CMS_ID_SHIPPING'),
			'template_type'         => $params['type']
		));
		
		return $this->display(__FILE__, 'displayProductPriceBlock.tpl');
		
	}
	
}
