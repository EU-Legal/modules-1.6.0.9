<?php

class AdminHomeController extends AdminHomeControllerCore
{
	public function ajaxProcessGetAdminHomeElement()
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Verbindung zu Prestashop.com unterbinden (GC_GERMAN_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('GC_GERMAN_PANIC_MODE'))
		{
			$this->content_only = true;
			$result = array();

			// SCREENCAST
			$result['screencast'] = '';

			// PREACTIVATION
			$result['partner_preactivation'] = '';

			// DISCOVER PRESTASHOP
			$result['discover_prestashop'] = '';
			$result['discover_prestashop'] .= '';
			$result['discover_prestashop'] .= '';

			$this->content = Tools::jsonEncode($result);
		
		}
		else
			parent::ajaxProcessGetAdminHomeElement();
	}
	
	public function getBlockPartners()
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Verbindung zu Prestashop.com unterbinden (GC_GERMAN_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('GC_GERMAN_PANIC_MODE'))
			return false;
		
		return getBlockPartners();
	}
	
	public function ajaxProcessSavePreactivationRequest()
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Verbindung zu Prestashop.com unterbinden (GC_GERMAN_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('GC_GERMAN_PANIC_MODE'))
			die();
		
		parent::ajaxProcessSavePreactivationRequest();
	}
	
	public function getBlockDiscover()
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Verbindung zu Prestashop.com unterbinden (GC_GERMAN_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('GC_GERMAN_PANIC_MODE'))
			return false;
		
		return getBlockDiscover();
	}
	
}

