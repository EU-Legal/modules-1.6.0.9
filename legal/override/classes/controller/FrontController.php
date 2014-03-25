<?php

class FrontController extends FrontControllerCore
{

	public function init()
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* CMS Seiten für alle FO Templates verfügbar machen
		* german = 1 für alle FO Templates
		*/
		
		if (self::$initialized)
			return;
		
		parent::init();
		
		$content_links = array(
			'contitions'   => array('cms_id_conditions',    'PS_CONDITIONS_CMS_ID',    'link_conditions'),
			'shipping'     => array('cms_id_shipping',      'LEGAL_CMS_ID_SHIPPING',      'link_shipping'),
			'legal'        => array('cms_id_legal',         'LEGAL_CMS_ID_LEGAL',         'link_legal'),
			'revocation'   => array('cms_id_revocation',    'LEGAL_CMS_ID_REVOCATION',    'link_revocation'),
			'privacy'      => array('cms_id_privacy',       'LEGAL_CMS_ID_PRIVACY',       'link_privacy'),
			'enviromental' => array('cms_id_environmental', 'LEGAL_CMS_ID_ENVIRONMENTAL', 'link_enviromental')
		);
		
		foreach($content_links as $a) {
			
			$link = $this->context->link->getCMSLink(Configuration::get($a[1]), null, true);
			
			if(strpos($link, '?'))
				$link = $link.'&content_only=1';
			else
				$link = $link.'?content_only=1';
			
			$this->context->smarty->assign(array(
				$a[0] => Configuration::get($a[1]),
				$a[2] => $link
			));
			
		}
		
		$this->context->smarty->assign(array(
			'german' => 1
		));
		
	}
	
}