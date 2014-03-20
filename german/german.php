<?php

/**
* (PrestaShop) German
* Modul für verbessere Rechtssicherheit im PrestaShop
* 
* @version       : 0.0.1
* @date          : 2014 03 20
* @author        : Markus Engel @ Onlineshop-Module.de | Julia Bengiev @ Silbersaiten.de
* @copyright     : 2014 Onlineshop-Module.de | Silbersaiten.de
* @contact       : info@onlineshop-module.de | info@silbersaiten.de
* @homepage      : www.onlineshop-module.de | www.silbersaiten.de
* @license       : OSL 3.0, see licence.txt
* @changelog     : see changelog.txt
* @compatibility : PS >= 1.6.0.5
*/

 
// TODO: 
// $this->hookDisplayProductAvailability($params): Genaues Datum der lieferbarkeit: Neues Modul? Wird wie konfiguriert?
 
// no direct access
if (!defined('_PS_VERSION_'))
	exit;
	
// german class
class German extends Module {
	
	/*******************************************************************************************************************
	*
	* Module vars and constants
	*
	*******************************************************************************************************************/
	
	// available languages
	public $languages = NULL;            
	
	// default shop language
	public $default_language_id = 1;               
	
	// supportet theme
	public $themes = array(           
		'bootstrap-german' => 'Bootstrap German', 
	);
	
	// default availability 'now'
	public $availableNowDefault = '2-3 Werktage';  
	
	// default availability 'later'
	public $availableLaterDefault = '7-10 Werktage'; 
	
	// supported modules
	public $modules = array(           
		'gc_ganalytics' => 'Google Analytics',
		'gc_newsletter' => 'Newsletter',
		'gc_blockcart'  => 'Warenkorb Block',
	);
	
	// new hooks to install
	public $hooks = array(
		'displayReorder'             => 'Reorder',                    // Hook unter Reorder Button (history.tpl)
		'displayProductAvailability' => 'Product Availability',       // Hook Produkt Verfügbarkeit (product.tpl, product-list.tpl, products-comparison.tpl, shopping-cart-product-line.tpl)
		'displayProductPrice'        => 'Product Price Display',      // Hook unter Produkt Preis Anzeige (product.tpl, product-list.tpl, products-comparison.tpl)
		'displayOldPrice'            => 'Product Old Price Display',  // Hook unter Alt-Preis Anzeige (product.tpl, product-list.tpl, products-comparison.tpl)
		'displayUnitPrice'           => 'Product Unit Price Display', // Hook für Einzelpreis (product.tpl, product-list.tpl, products-comparison.tpl)
	);
	
	// modules not compatible with german
	public $modules_not_compatible = array(
		'bankwire',
		'cheque',
		'blockcart',
	);
	
	// modules must install 
	public $modules_must_install = array(
		'blockcustomerprivacy',
	);
	
	// Cache
	private static $_cms_pages = array();
	
	/*******************************************************************************************************************
	*
	* Construct
	*
	*******************************************************************************************************************/
	
	public function __construct() {
		
	 	$this->name                   = 'german';               // module name
	 	$this->tab                    = 'administration';       // module backoffice tab
	 	$this->version                = '0.0.1';                // version
		$this->author                 = 'Onlineshop-Module.de'; // author
		$this->need_instance          = 0;                      // instance? No
		$this->ps_versions_compliancy = array(                  // module compliancy
			'min' => '1.6.0.4',
			'max' => '1.6.0.5'
		);
	 	$this->bootstrap              = true;
		
		parent::__construct();
		
		$this->displayName      = $this->l('(PrestaShop) German');
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
		
	}
	
	/*******************************************************************************************************************
	*
	* Install / Uninstall / Update
	*
	*******************************************************************************************************************/
	
	// install (PrestaShop) German
	public function install() {
		
		$return = true;
		
		// Scan overrides for existing functions 
		$this->_errors = array_merge($this->_errors, $this->checkOverrides());
		
		if(!empty($this->_errors))
			return false;
		
		// parent install (overrides, module itself, ...)
		if(!parent::install())
			return false;
		
		// install and register hooks
		$return &= $this->installHooks();
		$return &= $this->installRegisterHooks();
		
		// product availability
		foreach($this->languages as $language) {
			$values[$language['id_lang']] = $this->availableNowDefault;
		}
		
		if(!Configuration::updateValue('GC_AVAILABLE_NOW', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update').': GC_AVAILABLE_NOW';
		}
		
		$values = array(); 
		
		foreach($this->languages as $language) {
			$values[$language['id_lang']] = $this->availableLaterDefault;
		}
		
		if(!Configuration::updateValue('GC_AVAILABLE_LATER', $values)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update').': GC_AVAILABLE_LATER';
		}
		
		// deactivate OPC
		// TODO: notwendig?
		if(!Configuration::updateValue('PS_ORDER_PROCESS_TYPE', 0)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update order process type.');
		}
		
		// Häckchen bei letztem Bestellschriff anzeigen?
		if(!Configuration::updateValue('GC_CONDITIONS_INPUT', 0)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update order process type.');
		}
		
		if(!Configuration::updateValue('GC_PRIVACY_INPUT', 0)) {
			$return &= false;
			$this->_errors[] = $this->l('Could not update order process type.');
		}
		
		// add error translations
		if(is_file(_PS_TRANSLATIONS_DIR_.'de/errors.php')) {
			
			$rows_original = file(_PS_TRANSLATIONS_DIR_.'de/errors.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			
			$rows_new = file(_PS_MODULE_DIR_.$this->name.'/translations/de/errors.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			unset($rows_new[0]);
			
			$rows = array_merge($rows_original, $rows_new);
			
			$return &= (bool)file_put_contents(_PS_TRANSLATIONS_DIR_.'de/errors.php', implode("\n\r", $rows));
			
		}
		
		// alter database for price precitions
		$return &= DB::getInstance()->execute("
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
		");
		
		$return &= DB::getInstance()->execute("
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
		");
		
		// Mail Duplikator für Auftragsbestätigung: AGB muss bei Vertragsschluss gespeichert werden...)
		$return &= Configuration::updateValue('GC_GERMAN_OCMAILDBL', ''); 
		
		// Prestashop generiert class_index nicht automatisch nach installation, evtl wieder entfernen ab 1.6
		Autoload::getInstance()->generateIndex();
		
		return (bool)$return;
		
    }
	
	private function installHooks() {
		
		$return = true;
		
		foreach($this->hooks as $hook_name => $hook_title) {
			
			if(Hook::getIdByName($hook_name))
				continue;
			
			$hook = new Hook();
			$hook->name = $hook_name;
			$hook->title = $hook_title;
			$hook->position = true;
			$hook->live_edit = false;
			
			if(!$hook->add() ) {
				$return &= false;
				$this->_errors[] = $this->l('Could not install new hooks').': '.$hook_name;
			}
			
		}
		
		return $return;
		
	}
	
	private function installRegisterHooks() {
		
		$return = true;
		
		// TODO: evtl nur ein Hook für Preiszusätze (displayProductPrice + displayProductAvailability + displayOldPrice + displayUnitPrice)
		$return &= $this->registerHook('displayAdminHomeInfos');
		$return &= $this->registerHook('displayReorder');
		$return &= $this->registerHook('displayProductAvailability');
		$return &= $this->registerHook('displayProductPrice');
		$return &= $this->registerHook('displayOldPrice');
		$return &= $this->registerHook('displayUnitPrice');
			
		if(!$return)
			$this->_errors[] = $this->l('Could not register hooks');
		
		return $return;
		
	}
	
	// Deinstallation von (PrestaShop) German
	public function uninstall() {
		
		$return = true;
		
		$return &= parent::uninstall();
		
		// Prestashop generiert class_index nicht automatisch nach deinstallation, evtl wieder entfernen ab 1.6
		Autoload::getInstance()->generateIndex();
		
		return (bool)$return;
		
	}
	
	public function reset() {
		
		// TODO: Reset funktionen einbauen (reinstall)
		
	}
	
	/*******************************************************************************************************************
	*
	* Module Configuration
	*
	*******************************************************************************************************************/
	
	// Admin Anzeige
	public function getContent() {
		
		$html  = '';
		
		$html .= '<img src="'.$this->_path.'logo_gcgerman.jpg'.'" alt="GC German Logo" class="float" style="margin:0 20px 20px 0;" />';
		$html .= '<p>'.$this->l('Welcome to GC German.').'<br />'.$this->l('You can improve the security of your online store.').'</p>';
		$html .= '<p><strong>'.$this->l('Please be paient: There is no guarantee of a total secure online store to the law of your country.').'</strong></p>';
		$html .= '<p><a class="link" href="'.$this->_path.'licence.txt" target="_blank">'.$this->l('Please consider our license disclaimer.').'</a></p>';
		$html .= '<div class="clear">&nbsp;</div>';
		
		// Actions durchführen
		
		// TODO: trackingpixel wohin?
		if($postProcess = $this->postProcess() and !empty($postProcess));
			$postProcess .= '<img src="http://www.onlineshop-module.de/pml.gif?module='.$this->name.'&domain='.$this->context->shop->domain.'&version='.$this->version.'" style="display:none;" />';
		
		$html .= $postProcess;
		
		// Formular anzeigen
		$html .= $this->displayForm();
		
		return $html;
		
	}
	
	// Modulkonfiguration
	public function displayForm() {
		
		$html = '';
		
		// Einstellungen
		if(Tools::isSubmit('addDeliverynote') || Tools::isSubmit('updateDeliverynote')) {
			$html .= $this->displayFormDeliverynotes();
		}
		else {
			$html .= $this->displayFormSettings();
			$html .= $this->displayFormModules();
			$html .= $this->displayFormTheme();
			$html .= $this->displayListDeliverynotes();
		}
		
		return $html;
		
	}
	
	private function displayFormSettings() {
		
		$helper = new HelperOptions();
		
		// Helper Options
		$helper->required = false;
		$helper->id = Tab::getCurrentTabId(); //alway Tab::getCurrentTabId() at helper option
		
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
		
		$option_list = array(
			array(
				'title' => $this->l('Global Settings'),
				'info' => $this->l('Global Settings for all shops'),
				'icon' => 'icon-globe',
				'fields' => array(
					'GC_GERMAN_OCMAILDBL' => array(
						'type'  => 'text',
						'title' => $this->l('Order confirmation duplicator'),
						'desc'  => $this->l('Email address. Sends the order confirmation mail to this specific email address.')
					),
					'GC_GERMAN_PANIC_MODE' => array(
						'type'  => 'bool',
						'title' => $this->l('Silentmode'),
						'desc'  => $this->l('With this mode your PrestaShop wont connect to Prestashop.com')
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
					'PS_TAX' => array(
						'type'  => 'bool',
						'title' => $this->l('Tax'),
						'desc'  => $this->l('Activate the tax for your store.')
					),
					'PS_TAX_DISPLAY' => array(
						'type'  => 'bool',
						'title' => $this->l('Tax in block cart'),
						'desc'  => $this->l('Show the tax in your cart.')
					),
					'GC_GERMAN_SHIPTAXMETH' => array(
						'type'  => 'bool',
						'title' => $this->l('Shipping tax method'),
						'desc'  => $this->l('Calculates the average tax of all products for shipping instead of a fixed tax value.')
					),
					'GC_GERMAN_TAXMETH' => array(
						'type'  => 'bool',
						'title' => $this->l('tax method'),
						'desc'  => $this->l('Use average tax or most used tax of products in cart.')
					),
					'GC_CONDITIONS_INPUT' => array(
						'type'  => 'bool',
						'title' => $this->l('Conditions input'),
						'desc'  => $this->l('Shows the checkbox for conditions in the last order step.')
					),
					'GC_PRIVACY_INPUT' => array(
						'type'  => 'bool',
						'title' => $this->l('Privacy input'),
						'desc'  => $this->l('Shows the checkbox for privacy in the registration process.')
					),
					'GC_AVAILABLE_NOW' => array(
						'type'  => 'textLang',
						'title' => $this->l('Availabiliy "in stock"'),
						'desc'  => $this->l('Displayed text when in-stock default value. E.g.').' '.$this->availableNowDefault,
					),
					'GC_AVAILABLE_LATER' => array(
						'type'  => 'textLang',
						'title' => $this->l('Availabiliy "back-ordered"'),
						'desc'  => $this->l('Displayed text when allowed to be back-ordered default value. E.g.').' '.$this->availableLaterDefault,
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
				'fields' => array(
					'GC_CMS_ID_LEGAL' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Legal notice'),
					),
					'PS_CONDITIONS_CMS_ID' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Conditions'),
					),
					'GC_CMS_ID_REVOCATION' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Revocation'),
					),
					'GC_CMS_ID_PRIVACY' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Privacy policy'),
					),
					'GC_CMS_ID_ENVIRONMENTAL' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Environmental'),
					),
					'GC_CMS_ID_SHIPPING' => array(
						'type'       => 'select',
						'list'       => $this->getCMSPages(),
						'identifier' => 'id_cms',
						'title'      => $this->l('Delivery'),
					),
				),
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
		
		return $helper->generateOptions($option_list);
		
	}
	
	private function displayFormModules() {
		
		// TODO: Modulcheck einbauen
		
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
		
		$modules = array();
		foreach($this->modules as $name => $title) {
			$modules[] = array(
				'name'      => $name,
				'title'     => $title,
				'installed' => (bool)Module::isInstalled($name),
				'val'       => $name,
			);
			$helper->fields_value[] = array('module_'.$name => (bool)Module::isInstalled($name));
		}
		
		$fields_form = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Additional Modules'),
						'icon' => 'icon-puzzle-piece'
					),
					'input' => array(
						array(
							'type' => 'checkbox_module',
							'label' => $this->l('must have modules'),
							'name' => 'modules',
							'values' => array(
								'query'    => $modules,
								'id'       => 'name',
								'name'     => 'title',
								'disabled' => 'installed',
							),
						),
					),
					'submit' => array(
						'title' => $this->l('Add Modules'),
						'icon'  => 'process-icon-plus',
					)
				),
			),
		);
		
		return $helper->generateForm($fields_form);
		
	}
	
	private function displayFormTheme() {
		
		// TODO: Check Theme hooks vorhanden?
		
		$helper = new HelperForm();
		
		// Helper Form
		$helper->languages = $this->languages;
		$helper->default_form_language = $this->default_language_id;
		$helper->submit_action = false;
		
		// Helper
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->table = 'configuration';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->module = $this;
		$helper->title = null;
		
		$fields_form = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('Template Settings'),
						'icon' => 'icon-picture-o'
					),
					'input' => array(
						array(
							'type' => 'html',
							'label' => $this->l('Install Theme'),
							'name' => 'theme',
							'html' => '<input type="submit" name="submitAddTheme" value="'.$this->l('install theme').'" class="btn btn-primary" />',
							'desc'  => $this->l('The theme is the optical addition to (PrestaShop) German. After the installation please activate it for your stores.'),
						),
					),
				),
			),
		);
		
		return $helper->generateForm($fields_form);
		
	}
	
	private function displayListDeliverynotes() {
		
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
	
	private function displayFormDeliverynotes() {
		
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
		
		$fields_form = array(
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
		
		return $helper->generateForm($fields_form);
		
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
			if(!Configuration::updateValue('PS_TAX', (bool)Tools::getValue('PS_TAX')))
				$this->_errors[] = $this->l('Could not update').': PS_TAX';
			
			if(!Configuration::updateValue('PS_TAX_DISPLAY', (bool)Tools::getValue('PS_TAX_DISPLAY'))) 
				$this->_errors[] = $this->l('Could not update').': PS_TAX_DISPLAY';
			
			if(!Configuration::updateValue('GC_GERMAN_SHIPTAXMETH', (bool)Tools::getValue('GC_GERMAN_SHIPTAXMETH'))) 
				$this->_errors[] = $this->l('Could not update').': GC_GERMAN_SHIPTAXMETH';
				
			if(!Configuration::updateValue('GC_CONDITIONS_INPUT', (int)Tools::getValue('GC_CONDITIONS_INPUT'))) 
				$this->_errors[] = $this->l('Could not update').': GC_CONDITIONS_INPUT';
			
			if(!Configuration::updateValue('GC_PRIVACY_INPUT', (int)Tools::getValue('GC_PRIVACY_INPUT'))) 
				$this->_errors[] = $this->l('Could not update').': GC_PRIVACY_INPUT';
			
			// Produktverfügbarkeit
			$values = array(); 
			
			foreach($this->languages as $language) {
				$values[$language['id_lang']] = Tools::getValue('GC_AVAILABLE_NOW_'.$language['id_lang']);
			}
			
			if(!Configuration::updateValue('GC_AVAILABLE_NOW', $values))
				$this->_errors[] = $this->l('Could not update').': GC_AVAILABLE_NOW';
			
			$values = array(); 
			
			foreach($this->languages as $language) {
				$values[$language['id_lang']] = Tools::getValue('GC_AVAILABLE_LATER_'.$language['id_lang']);
			}
			
			if(!Configuration::updateValue('GC_AVAILABLE_LATER', $values))
				$this->_errors[] = $this->l('Could not update').': GC_AVAILABLE_LATER';
			
			// CMS IDs festlegen
			if(!Configuration::updateValue('GC_CMS_ID_LEGAL', (int)Tools::getValue('GC_CMS_ID_LEGAL')))
				$this->_errors[] = $this->l('Could not update').': GC_CMS_ID_LEGAL';
			
			if(!Configuration::updateValue('PS_CONDITIONS_CMS_ID', (int)Tools::getValue('PS_CONDITIONS_CMS_ID')))
				$this->_errors[] = $this->l('Could not update').': PS_CONDITIONS_CMS_ID';
			
			if(!Configuration::updateValue('GC_CMS_ID_REVOCATION', (int)Tools::getValue('GC_CMS_ID_REVOCATION')))
				$this->_errors[] = $this->l('Could not update').': GC_CMS_ID_REVOCATION';
			
			if(!Configuration::updateValue('GC_CMS_ID_PRIVACY', (int)Tools::getValue('GC_CMS_ID_PRIVACY')))
				$this->_errors[] = $this->l('Could not update').': GC_CMS_ID_PRIVACY';
			
			if(!Configuration::updateValue('GC_CMS_ID_ENVIRONMENTAL', (int)Tools::getValue('GC_CMS_ID_ENVIRONMENTAL')))
				$this->_errors[] = $this->l('Could not update').': GC_CMS_ID_ENVIRONMENTAL';
			
			if(!Configuration::updateValue('GC_CMS_ID_SHIPPING', (int)Tools::getValue('GC_CMS_ID_SHIPPING')))
				$this->_errors[] = $this->l('Could not update').': GC_CMS_ID_SHIPPING';
			
			// Panikmodus und Bestellstatus ID Auftragsbestätigung
			if(!Configuration::updateGlobalValue('GC_GERMAN_PANIC_MODE', (bool)Tools::getValue('GC_GERMAN_PANIC_MODE')))
				$this->_errors[] = $this->l('Could not update').': GC_GERMAN_PANIC_MODE';
			
			$email = Tools::getValue('GC_GERMAN_OCMAILDBL');
			
			if($email == '' or Validate::isEmail($email)) {
				if(!Configuration::updateGlobalValue('GC_GERMAN_OCMAILDBL', $email))
					$this->_errors[] = $this->l('Could not update').': GC_GERMAN_OCMAILDBL';
			}
			elseif($email != '' )
				$this->_errors[] = $this->l('this is no valid email address for the orderconfirmation mail duplicator');
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Settings updated'));
			
		}
		
		if (Tools::isSubmit('submitAddCMSPages')) {
			
			//$files = Tools::getValue('cms');
			$files = array(
				'legalnotice',
				'conditions',
				'revocation',
				'privacy',
				'environmental',
				'shipping',
			);
			
			foreach($files as $name) {
				
				if($content = @file($this->local_path.'cms/'.$name.'.txt')) {
					
					$cms = new CMS();
					$cms->active = true;
					$cms->id_cms_category = 1;
					
					$content[4] = preg_replace('#src="(.*)"#u', 'src="'.Context::getContext()->shop->getBaseURL().'\\1"', $content[4]);
					
					foreach($this->languages as $language) {
						
						$cms->meta_title[$language['id_lang']]       = trim($content[0]);
						$cms->meta_description[$language['id_lang']] = trim($content[1]);
						$cms->meta_keywords[$language['id_lang']]    = trim($content[2]);
						$cms->link_rewrite[$language['id_lang']]     = trim($content[3]);
						$cms->content[$language['id_lang']]          = trim($content[4]);
						
					}
					
					if(!$cms->add())
						$this->_errors[] = $this->l('Could not add new cms page').': '.$name;
					
					if($name == 'legalnotice')   Configuration::updateValue('GC_CMS_ID_LEGAL',         $cms->id);
					if($name == 'conditions')    Configuration::updateValue('PS_CONDITIONS_CMS_ID',    $cms->id);
					if($name == 'revocation')    Configuration::updateValue('GC_CMS_ID_REVOCATION',    $cms->id);
					if($name == 'privacy')       Configuration::updateValue('GC_CMS_ID_PRIVACY',       $cms->id);
					if($name == 'environmental') Configuration::updateValue('GC_CMS_ID_ENVIRONMENTAL', $cms->id);
					if($name == 'shipping')      Configuration::updateValue('GC_CMS_ID_SHIPPING',      $cms->id);
				
				}
				else
					$this->_errors[] = $this->l('Could not open file').': cms/'.$name.'.txt';
				
				
			}
			
			try {
				$this->rcopy('modules/'.$this->name.'/img/batterieverordnung.jpg', 'img/cms/batterieverordnung.jpg', array('root' => _PS_ROOT_DIR_));
			}
			catch(Exception $e) {
				$this->_errors[] = $this->l('Could not copy').': /img/batterieverordnung.jpg';
			}
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('CMS Pages created'));
			
		}
		
		if (Tools::isSubmit('submitAddModules')) {
			
			$modules = Tools::getValue('modules');
			$dir = dirname(__FILE__).'/modules/';
			
			foreach($modules as $module) {
				
				if(!is_dir(_PS_MODULE_DIR_.$module) and !Tools::ZipExtract($dir.$module.'.zip', _PS_MODULE_DIR_)) {
					$this->_errors[] = $this->l('Could not extract file').': '.$module.'.zip';
					continue;
				}
				
				if(!$instance = self::getInstanceByName($module) or !$instance->install()) {
					
					if(is_array($instance->_errors))
						$this->_errors = array_merge($this->_errors, $instance->_errors);
					
				}
				
			}
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Modules installed'));
			
		}
		
		if(Tools::isSubmit('submitAddTheme')) {
			
			$theme_name  = $this->themes[0][0];
			$theme_title = $this->themes[0][1];
			
			$dir = dirname(__FILE__).'/themes/';
			
			if(!is_dir(_PS_ALL_THEMES_DIR_.$theme_name) and !Tools::ZipExtract($dir.$theme_name.'.zip', _PS_ALL_THEMES_DIR_))
				$this->_errors[] = $this->l('Could not extract file').': '.$theme_name.'.zip';
			else {
				$theme                       = new Theme();
				$theme->name                 = $theme_title;
				$theme->directory            = $theme_name;
				$theme->responsive           = true;
				$theme->default_left_column  = true;
				$theme->default_right_column = false;
				$theme->product_per_page     = 12;
				
				if(!$theme->add())
					$this->_errors[] = $this->l('Could not install theme').': '.$theme_name;
				
			}
			
			if(count($this->_errors) <= 0)
				return $this->displayConfirmation($this->l('Theme installed'));
			
		}
		
		if(Tools::isSubmit('submitAddDeliverynote')) {
			
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
			
			if(!Configuration::updateValue('GC_GERMAN_DN_'.$id_carrier.'_'.$id_module.'_'.$id_zone, $notes))
				$this->_errors[] = $this->l('Could not update').': GC_GERMAN_DN_'.$id_carrier.'_'.$id_module.'_'.$id_zone;
			
			if(count($this->_errors) <= 0)
				Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
			
		}
		
		if(Tools::isSubmit('deleteDeliverynote')) {
			
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
	
	private function getCMSPages() {
		
		if(empty(self::$_cms_pages)) {
			
			$result = CMS::getCMSPages($this->default_language_id, null, false);
			
			foreach($result as $key => $row)
				self::$_cms_pages[] = array('id_cms' => $row['id_cms'], 'name' => $row['meta_title']);
			
		}
		
		return self::$_cms_pages;
		
	}
	
	private function checkHooksInTheme() {
		// TODO !
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
				WHERE c.`name` LIKE 'GC_GERMAN_DN_%'
			";
		
		$configuration_ids = DB::getInstance()->executeS($sql);
		
		foreach($configuration_ids as $configuration_id) {
			
			$configuration = new Configuration($configuration_id['id_configuration']);
			
			if(!$configuration)
				continue;
			
			$m = array();
			
			if(preg_match('#GC_GERMAN_DN_([0-9]+)_([0-9]+)_([0-9]+)#', $configuration->name, $m)) {
				
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
		| GC German 1.5.6.0 | 20131028
		| Die Do-While schleife benötigt uniqid als Argument
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
		| GC German 1.5.6.0 | 20131022
		| Die Do-While schleife benötigt uniqid als Argument
		| Bei löschen von overrides müssen auch Klassenkostanten 'const' entfernt werden
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
	
	public function checkForUpdate() {
		
		// TODO: Updateserver??
		
		$url = 'http://www.onlineshop-module.de/osm_module.php?module='.$this->name.'&version='.$this->version;
		
		if($response = Tools::file_get_contents($url) and $response == '1')
			return true;
		
		return false;
		
	}
	
	/*******************************************************************************************************************
	*
	* Hooks
	*
	*******************************************************************************************************************/
	
	public function hookDisplayAdminHomeInfos() {
		
		if($this->checkForUpdate())
			return '<div class="warning warn" style="margin-bottom:10px;clear:both;">'.sprintf($this->l('There is a new version available of %s'), $this->displayName).': <a style="text-decoration: underline;" href="http://www.onlineshop-module.de" target="_blank">http://www.onlineshop-module.de</a></div>';
		
	}
	
	public function hookDisplayReorder($params) {
		
		return $this->display(__FILE__, 'displayReorder.tpl');
		
	}
	
	public function hookDisplayProductAvailability($params) {
		
		if($params['id_product'] instanceof Product)
			$product = $params['id_product'];
		else
			$product = new Product((int)$params['id_product'], true, $this->context->cookie->id_lang);
		
		// TODO: Genaues Datum der lieferbarkeit: Neues Modul? Wird wie konfiguriert?
		
		$this->smarty->assign(array(
			'product' => $product,  
			'allow_oosp' => $product->isAvailableWhenOutOfStock((int)$product->out_of_stock),
		));
		
		// return $this->display(__FILE__, 'displayProductAvailability.tpl');
		
	}
	
	public function hookDisplayProductPrice($params) {
		
		/* wird noch nicht benötigt */
		//if($params['id_product'] instanceof Product)
			//$product = $params['id_product'];
		//else
			//$product = new Product((int)$params['id_product'], true, $this->context->cookie->id_lang);
		
		$this->smarty->assign(array(
			'tax_enabled'       => Configuration::get('PS_TAX'),  
			'cms_id_shipping'   => Configuration::get('GC_CMS_ID_SHIPPING'),
		));
		
		return $this->display(__FILE__, 'displayProductPrice.tpl');
		
	}
	
	public function hookDisplayOldPrice($params) {
		
		$priceDisplay = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
		
		if($params['id_product'] instanceof Product)
			$product = $params['id_product'];
		else
			$product = new Product((int)$params['id_product'], true, $this->context->cookie->id_lang);
		
		if(!$priceDisplay || $priceDisplay == 2) {
			$productPrice = $product->getPrice(true);
			$productPriceWithoutReduction = $product->getPriceWithoutReduct(false);
		}
		else {
			$productPrice = $product->getPrice(false);
			$productPriceWithoutReduction = $product->getPriceWithoutReduct(true);
		}
		
		$this->smarty->assign(array(
			'product' => $product,  
			'priceDisplay' => $priceDisplay,  
			'productPriceWithoutReduction' => $productPriceWithoutReduction,
			'productPrice' => $productPrice,
		));
		
		return $this->display(__FILE__, 'displayOldPrice.tpl');
		
	}
	
	public function hookDisplayUnitPrice($params) {
		
		$priceDisplay = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
		
		if($params['id_product'] instanceof Product)
			$product = $params['id_product'];
		else
			$product = new Product((int)$params['id_product'], true, $this->context->cookie->id_lang);
		
		if(!$priceDisplay || $priceDisplay == 2) {
			$productPrice = $product->getPrice(true);
			$productPriceWithoutReduction = $product->getPriceWithoutReduct(false);
		}
		else {
			$productPrice = $product->getPrice(false);
			$productPriceWithoutReduction = $product->getPriceWithoutReduct(true);
		}
		
		$this->smarty->assign(array(
			'product' => $product,  
			'priceDisplay' => $priceDisplay,  
			'productPriceWithoutReduction' => $productPriceWithoutReduction,
			'productPrice' => $productPrice,
		));
		
		return $this->display(__FILE__, 'displayUnitPrice.tpl');
		
	}
	
}
