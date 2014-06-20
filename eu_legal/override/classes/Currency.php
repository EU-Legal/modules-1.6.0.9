<?php
class Currency extends CurrencyCore
{
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
	parent::__construct($id, $id_lang, $id_shop);
	
	Hook::exec('currencyInstantiated', array('currency' => &$this));
    }
    
}