<?php

/**
* Legal
* Better security for german merchants.
* 
* @version       : 0.0.1
* @date          : 2014 03 20
* @author        : Markus Engel @ Onlineshop-Module.de | George June @ Silbersaiten.de
* @copyright     : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
* @contact       : info@onlineshop-module.de | info@silbersaiten.de
* @homepage      : www.onlineshop-module.de | www.silbersaiten.de
* @license       : http://opensource.org/licenses/osl-3.0.php
* @changelog     : see changelog.txt
* @compatibility : PS >= 1.6.0.5
*/

// TODO:
// Copy controllers/admin/templates/ override from module to ps

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
	
	public $languages = array();            
	public $default_language_id = 1;               
	public $theme = array();
	public $deliveryNowDefault = '';  
	public $deliveryLaterDefault = ''; 
	public $hooks = array();
	public $modules_not_compatible = array();
	public $modules_must_install = array();
	public $modules = array();
	public $cms_pages = array();
	public $config_prefix = '';
	
	
}
