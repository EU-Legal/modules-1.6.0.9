<?php

class Tools extends ToolsCore
{
	
	public static function dieObject($object, $kill = true)
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Backtrace anzeigen
		*/
		
		echo '<xmp style="text-align: left;">';
		print_r($object);
		echo '</xmp><br />';
		
		if($kill)
			Tools::debug_backtrace();
		
		if ($kill)
			die('END');
		return $object;
	}
	
	public static function debug_backtrace($start = 0, $limit = null)
	{
		
		/*
		| GC German 1.5.6.0 | 20140121
		| Backtrace anzeigen
		*/
		
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		
	}
	
	public static function addonsRequest($request, $params = array())
	{
		
		/*
		| GC German 1.5.6.0 | 20131022
		| Verbindungen zu Prestashop.com unterbinden (GC_GERMAN_PANIC_MODE)
		*/
		
		if(Configuration::getGlobalValue('GC_GERMAN_PANIC_MODE'))
			return false;
		
		return parent::addonsRequest($request, $params);
	}
	
}

