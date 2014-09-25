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

class Mail extends MailCore
{
	public static function Send($id_lang, $template, $subject, $template_vars, $to, $to_name = null, $from = null, $from_name = null, $file_attachment = null, $mode_smtp = null, $template_path = _PS_MAIL_DIR_, $die = false, $id_shop = null, $bcc = null)
	{	
		/*
		* EU-Legal
		* CMS pages available for all emails => txt and html
		*/
		
		$additional_cms = array(
			'conditions'    => 'PS_CONDITIONS_CMS_ID',    // Legal: Condition
			'revocation'    => 'LEGAL_CMS_ID_REVOCATION',    // Legal: Revocation
			'revocationform'=> 'LEGAL_CMS_ID_REVOCATIONFORM',    // Legal: Revocation Form
			'privacy'       => 'LEGAL_CMS_ID_PRIVACY',       // Legal: Privacy
			'environmental' => 'LEGAL_CMS_ID_ENVIRONMENTAL', // Legal: Environmental
			'legal'         => 'LEGAL_CMS_ID_LEGAL' // Legal: Imprint
		);
		
		$type = Configuration::get('PS_MAIL_TYPE');
		
		foreach($additional_cms as $key => $row) {
			
			$html = CMS::getContentFromId(Configuration::get($row), (int)$id_lang);
			
			if($type == Mail::TYPE_BOTH or $type == Mail::TYPE_HTML)
				$template_vars['{cms_'.$key.'}'] = $html;
			if($type == Mail::TYPE_BOTH or $type == Mail::TYPE_TEXT)
				$template_vars['{cms_'.$key.'_txt}'] = strip_tags($html);
			
		}
		
		return parent::Send(
			$id_lang, 
			$template, 
			$subject, 
			$template_vars, 
			$to, 
			$to_name, 
			$from, 
			$from_name, 
			$file_attachment, 
			$mode_smtp, 
			$template_path, 
			$die, 
			$id_shop,
			$bcc
		);
		
	}
	
}