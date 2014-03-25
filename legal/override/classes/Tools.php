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
	
}

