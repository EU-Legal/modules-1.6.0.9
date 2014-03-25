<?php

class Tools extends ToolsCore
{
	
	public static function dieObject($object, $kill = true)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Backtrace anzeigen
		*/
		
		echo '<xmp style="text-align: left;">';
		print_r($object);
		echo '</xmp><br />';

		/* Backtrace anzeigen */
		if($kill)
			Tools::debug_backtrace();
		
		if ($kill)
			die('END');

		return $object;
	}
	
	public static function debug_backtrace($start = 0, $limit = null)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Backtrace anzeigen ohne formatierungen und kürzungen
		*/
		
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		
	}
	
	public static function addonsRequest($request, $params = array())
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Verbindungen zu Prestashop.com unterbinden (LEGAL_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('LEGAL_PANIC_MODE'))
			return false;
		
		return parent::addonsRequest($request, $params);
	}
	
	public static function getRemoteAddr()
	{
		
		/*
		* Legal 0.0.1 | 20140325
		* delete last octet from ip address
		*/
		
		// get ip address from parent function
		$remote_address = parent::getRemoteAddr();
		
		$m = array();
		
		// VERY simple way to detect proper ip format -> only the first three octets required
		if(preg_match('#^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\.[0-9]{1,3}$#', $remote_address, $m)) {
			
			// if first three octetts found
			if(isset($m[1])) {
				
				// add last octet with zero value to the first three octets
				return $m[1].'.0';
			
			}
			
		}
		
		// no valid ip address: return default / parent;
		return $remote_address;
		
	}
	
}

